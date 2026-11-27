<?php
require_once __DIR__ . '/../config/fungsi.php';
require_once __DIR__ . '/../config/midtrans.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Metode tidak diizinkan.']);
        exit;
    }

    $csrfInput   = (string)($_POST['csrf'] ?? '');
    $csrfSession = (string)($_SESSION['csrf'] ?? '');
    if ($csrfSession === '' || $csrfInput === '' || !hash_equals($csrfSession, $csrfInput)) {
        http_response_code(419);
        echo json_encode(['ok' => false, 'message' => 'Token CSRF tidak valid. Muat ulang halaman.']);
        exit;
    }

    if (!function_exists('user') || !user()) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'Silakan masuk terlebih dahulu untuk voting.']);
        exit;
    }

    $period = function_exists('active_period') ? active_period($pdo) : null;
    if (!$period) {
        echo json_encode(['ok' => false, 'message' => 'Periode voting sedang tidak aktif.']);
        exit;
    }

    $kontestanId = (int)($_POST['kontestan_id'] ?? 0);
    $jumlahSuara = (int)($_POST['jumlah_suara'] ?? 0);

    if ($kontestanId <= 0 || $jumlahSuara < 1) {
        echo json_encode(['ok' => false, 'message' => 'Pilih kontestan dan jumlah suara minimal 1.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, nama, kategori, nomor_peserta FROM kontestan WHERE id = ? LIMIT 1');
    $stmt->execute([$kontestanId]);
    $kontestan = $stmt->fetch();

    if (!$kontestan) {
        echo json_encode(['ok' => false, 'message' => 'Kontestan tidak ditemukan.']);
        exit;
    }

    $uStmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $uStmt->execute([user()['id']]);
    $uRow = $uStmt->fetch() ?: [];

    $phone     = $uRow['no_hp'] ?? ($uRow['telepon'] ?? ($uRow['hp'] ?? '081234567890'));
    $userName  = $uRow['nama'] ?? (user()['nama'] ?? 'Pemilih');
    $userEmail = $uRow['email'] ?? (user()['email'] ?? 'pemilih@example.com');

    $hargaSatuan = defined('VOTING_HARGA_PER_SUARA') ? (int)VOTING_HARGA_PER_SUARA : 3500;
    $nominal     = $jumlahSuara * $hargaSatuan;
    $orderId     = 'KS-VOTE-' . date('YmdHis') . '-' . (int)user()['id'] . '-' . random_int(100, 999);

    $trxCols = $pdo->query('SHOW COLUMNS FROM transaksi_voting')->fetchAll(PDO::FETCH_COLUMN);

    $insertData = [
        'order_id'     => $orderId,
        'user_id'      => (int)user()['id'],
        'kontestan_id' => (int)$kontestan['id'],
        'jumlah_suara' => $jumlahSuara,
        'nominal'      => $nominal,
        'status'       => 'PENDING',
    ];
    if (in_array('kategori', $trxCols, true)) {
        $insertData['kategori'] = $kontestan['kategori'];
    }
    if (in_array('harga_satuan', $trxCols, true)) {
        $insertData['harga_satuan'] = $hargaSatuan;
    }

    $colsSql      = implode(', ', array_keys($insertData));
    $placeholders = implode(', ', array_fill(0, count($insertData), '?'));
    $ins          = $pdo->prepare("INSERT INTO transaksi_voting ($colsSql) VALUES ($placeholders)");
    $ins->execute(array_values($insertData));
    $trxId = (int)$pdo->lastInsertId();

    if (!function_exists('midtrans_create_snap_token')) {
        echo json_encode(['ok' => false, 'message' => 'Fungsi midtrans_create_snap_token belum tersedia di config/midtrans.php.']);
        exit;
    }

    $payload = [
        'transaction_details' => [
            'order_id'     => $orderId,
            'gross_amount' => $nominal,
        ],
        'item_details' => [[
            'id'       => 'KS-' . $kontestan['id'],
            'price'    => $hargaSatuan,
            'quantity' => $jumlahSuara,
            'name'     => substr('Suara ' . $kontestan['kategori'] . ' #' . $kontestan['nomor_peserta'] . ' ' . $kontestan['nama'], 0, 50),
        ]],
        'customer_details' => [
            'first_name' => $userName,
            'email'      => $userEmail,
            'phone'      => $phone,
        ],
    ];

    $res = midtrans_create_snap_token($payload);
    if (empty($res['ok'])) {
        $pdo->prepare('UPDATE transaksi_voting SET status = "FAILED" WHERE id = ?')->execute([$trxId]);
        echo json_encode(['ok' => false, 'message' => $res['message'] ?? 'Gagal membuat token pembayaran Midtrans.']);
        exit;
    }

    $snapToken   = $res['data']['token'] ?? null;
    $redirectUrl = $res['data']['redirect_url'] ?? null;

    if (!$snapToken) {
        $pdo->prepare('UPDATE transaksi_voting SET status = "FAILED" WHERE id = ?')->execute([$trxId]);
        echo json_encode(['ok' => false, 'message' => 'Snap token tidak diterima dari Midtrans.']);
        exit;
    }

    if (in_array('payment_url', $trxCols, true)) {
        $pdo->prepare('UPDATE transaksi_voting SET payment_url = ? WHERE id = ?')->execute([$redirectUrl, $trxId]);
    }
    if (in_array('snap_token', $trxCols, true)) {
        $pdo->prepare('UPDATE transaksi_voting SET snap_token = ? WHERE id = ?')->execute([$snapToken, $trxId]);
    }

    echo json_encode([
        'ok'           => true,
        'order_id'     => $orderId,
        'snap_token'   => $snapToken,
        'redirect_url' => $redirectUrl,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'message' => 'Error server: ' . $e->getMessage(),
    ]);
}