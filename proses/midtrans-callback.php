<?php
require_once __DIR__ . '/../config/fungsi.php';
require_once __DIR__ . '/../config/midtrans.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo 'Invalid JSON';
    exit;
}

$orderId           = trim((string)($data['order_id'] ?? ''));
$statusCode        = trim((string)($data['status_code'] ?? ''));
$grossAmount       = trim((string)($data['gross_amount'] ?? ''));
$signatureKey      = trim((string)($data['signature_key'] ?? ''));
$transactionStatus = trim((string)($data['transaction_status'] ?? ''));
$fraudStatus       = trim((string)($data['fraud_status'] ?? ''));
$paymentType       = trim((string)($data['payment_type'] ?? ''));
$transactionId     = trim((string)($data['transaction_id'] ?? ''));

if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
    http_response_code(400);
    echo 'Missing fields';
    exit;
}

$expectedSig = hash('sha512', $orderId . $statusCode . $grossAmount . MIDTRANS_SERVER_KEY);
if (!hash_equals($expectedSig, $signatureKey)) {
    http_response_code(403);
    echo 'Invalid signature';
    exit;
}

$isPaid = false;
if ($transactionStatus === 'settlement') {
    $isPaid = true;
} elseif ($transactionStatus === 'capture' && $fraudStatus !== 'challenge') {
    $isPaid = true;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT * FROM transaksi_voting WHERE order_id = ? FOR UPDATE');
    $stmt->execute([$orderId]);
    $trx = $stmt->fetch();

    if (!$trx) {
        $pdo->rollBack();
        http_response_code(404);
        echo 'Order not found';
        exit;
    }

    if ((int)$grossAmount !== (int)$trx['nominal']) {
        $pdo->rollBack();
        http_response_code(400);
        echo 'Amount mismatch';
        exit;
    }

    if ($trx['status'] === 'PAID') {
        $pdo->commit();
        echo 'OK';
        exit;
    }

    if ($isPaid) {
        $upd = $pdo->prepare('
            UPDATE transaksi_voting
            SET status = "PAID",
                payment_method = COALESCE(NULLIF(?, ""), payment_method),
                reference = COALESCE(NULLIF(?, ""), reference),
                paid_at = NOW()
            WHERE id = ?
        ');
        $upd->execute([$paymentType, $transactionId, $trx['id']]);

        $cek = $pdo->prepare('SELECT id FROM voting WHERE transaksi_id = ? LIMIT 1');
        $cek->execute([$trx['id']]);
        if (!$cek->fetch()) {
            $insVote = $pdo->prepare('
                INSERT INTO voting (transaksi_id, user_id, kontestan_id, kategori, jumlah_suara, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $insVote->execute([
                $trx['id'],
                $trx['user_id'],
                $trx['kontestan_id'],
                $trx['kategori'],
                $trx['jumlah_suara'],
            ]);
        }

        $pdo->commit();
        echo 'OK';
        exit;
    }

    if (in_array($transactionStatus, ['expire'], true)) {
        $pdo->prepare('UPDATE transaksi_voting SET status = "EXPIRED" WHERE id = ?')->execute([$trx['id']]);
    } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure'], true)) {
        $pdo->prepare('UPDATE transaksi_voting SET status = "FAILED" WHERE id = ?')->execute([$trx['id']]);
    }

    $pdo->commit();
    echo 'OK';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo 'Error';
}