<?php
require_once __DIR__ . '/../config/fungsi.php';
require_once __DIR__ . '/../config/midtrans.php';

if (!user()) {
    flash('Silakan masuk terlebih dahulu untuk melakukan voting.', 'warning');
    redirect('auth/login.php');
}

$page_title = 'Voting · Kakang Senduk Ponorogo';
$nav_active = 'voting';
$period = active_period($pdo);

$kontestanList = $pdo->query('
    SELECT k.*, COALESCE(SUM(v.jumlah_suara), 0) AS total_suara
    FROM kontestan k
    LEFT JOIN voting v ON v.kontestan_id = k.id
    GROUP BY k.id
    ORDER BY k.kategori ASC, CAST(k.nomor_peserta AS UNSIGNED) ASC, k.id ASC
')->fetchAll();

$riwayatStmt = $pdo->prepare('
    SELECT t.*, k.nama AS nama_kontestan, k.nomor_peserta
    FROM transaksi_voting t
    JOIN kontestan k ON k.id = t.kontestan_id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
    LIMIT 10
');
$riwayatStmt->execute([user()['id']]);
$riwayatTrx = $riwayatStmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-4">
  <div>
    <div class="eyebrow">Dukungan Resmi</div>
    <h1 class="section-title mb-0">Voting Kakang Senduk</h1>
  </div>
  <div class="text-secondary small">
    Tarif: <strong class="text-dark">Rp<?= number_format(VOTING_HARGA_PER_SUARA, 0, ',', '.') ?></strong> / suara &middot; Bebas jumlah suara
  </div>
</div>

<?php if (!$period): ?>
  <div class="alert alert-warning">Periode voting sedang tidak aktif saat ini.</div>
<?php else: ?>
  <div class="row g-4 mb-5">
    <div class="col-lg-7">
      <div class="panel">
        <h2 class="h6 fw-bold mb-3">1. Pilih Kontestan &amp; Jumlah Suara</h2>
        <form id="formVotingMidtrans" method="post" action="<?= e(base_url('proses/buat-transaksi.php')) ?>">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div class="mb-3">
            <label for="kontestan_id" class="form-label fw-medium">Kontestan</label>
            <select id="kontestan_id" name="kontestan_id" class="form-select" required>
              <option value="">-- Pilih Finalis --</option>
              <?php foreach ($kontestanList as $k): ?>
                <option value="<?= (int)$k['id'] ?>">
                  [<?= e($k['kategori']) ?> #<?= e($k['nomor_peserta']) ?>] <?= e($k['nama']) ?> (<?= (int)$k['total_suara'] ?> suara)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="jumlah_suara" class="form-label fw-medium">Jumlah Suara</label>
            <input type="number" id="jumlah_suara" name="jumlah_suara" class="form-control" min="1" step="1" value="1" required>
            <div class="d-flex flex-wrap gap-2 mt-2">
              <?php foreach ([1, 5, 10, 25, 50] as $preset): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm preset-suara" data-val="<?= $preset ?>">
                  +<?= $preset ?> Suara
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="p-3 rounded-3 mb-4" style="background-color: var(--surface-subtle); border: 1px solid var(--border-subtle);">
            <div class="d-flex justify-content-between small text-secondary mb-1">
              <span>Harga per suara</span>
              <span>Rp<?= number_format(VOTING_HARGA_PER_SUARA, 0, ',', '.') ?></span>
            </div>
            <div class="d-flex justify-content-between small text-secondary mb-2">
              <span>Jumlah suara</span>
              <span id="ringkasanSuara">1 suara</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold">Total Pembayaran</span>
              <span id="ringkasanTotal" class="fs-5 fw-bold text-primary">Rp3.500</span>
            </div>
          </div>

          <div id="votingAlert" class="alert alert-danger d-none mb-3"></div>

          <button type="submit" id="btnBayarMidtrans" class="btn btn-primary btn-lg w-100">
            Bayar &amp; Kirim Suara via Midtrans
          </button>
        </form>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="panel">
        <h2 class="h6 fw-bold mb-3">2. Riwayat Transaksi Anda</h2>
        <?php if (empty($riwayatTrx)): ?>
          <p class="text-secondary small mb-0">Belum ada transaksi voting pada akun ini.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Kontestan</th>
                  <th>Suara</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($riwayatTrx as $trx): ?>
                  <?php
                    switch ($trx['status']) {
                        case 'PAID':
                            $badge = 'text-bg-success';
                            break;
                        case 'PENDING':
                            $badge = 'text-bg-warning';
                            break;
                        case 'EXPIRED':
                            $badge = 'text-bg-secondary';
                            break;
                        default:
                            $badge = 'text-bg-danger';
                            break;
                    }
                  ?>
                  <tr>
                    <td>
                      <div class="fw-medium small"><?= e($trx['nama_kontestan']) ?></div>
                      <div class="text-secondary" style="font-size:.75rem"><?= e($trx['order_id']) ?></div>
                    </td>
                    <td class="small"><?= (int)$trx['jumlah_suara'] ?></td>
                    <td class="small">Rp<?= number_format((int)$trx['nominal'], 0, ',', '.') ?></td>
                    <td><span class="badge <?= $badge ?>"><?= e($trx['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<script src="<?= e(midtrans_snap_js_url()) ?>" data-client-key="<?= e(MIDTRANS_CLIENT_KEY) ?>"></script>
<script>
(function() {
  const hargaPerSuara = <?= (int)VOTING_HARGA_PER_SUARA ?>;
  const inputJumlah = document.getElementById('jumlah_suara');
  const ringkasanSuara = document.getElementById('ringkasanSuara');
  const ringkasanTotal = document.getElementById('ringkasanTotal');
  const form = document.getElementById('formVotingMidtrans');
  const alertBox = document.getElementById('votingAlert');
  const btnBayar = document.getElementById('btnBayarMidtrans');

  function formatRupiah(angka) {
    return 'Rp' + Number(angka || 0).toLocaleString('id-ID');
  }

  function updateKalkulasi() {
    if (!inputJumlah) return;
    let val = parseInt(inputJumlah.value, 10);
    if (isNaN(val) || val < 1) val = 1;
    ringkasanSuara.textContent = val + ' suara';
    ringkasanTotal.textContent = formatRupiah(val * hargaPerSuara);
  }

  document.querySelectorAll('.preset-suara').forEach((btn) => {
    btn.addEventListener('click', () => {
      const tambah = parseInt(btn.getAttribute('data-val'), 10) || 1;
      const saatIni = parseInt(inputJumlah.value, 10) || 0;
      inputJumlah.value = saatIni + tambah;
      updateKalkulasi();
    });
  });

  if (inputJumlah) {
    inputJumlah.addEventListener('input', updateKalkulasi);
    updateKalkulasi();
  }

  if (form) {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      alertBox.classList.add('d-none');
      alertBox.textContent = '';

      const fd = new FormData(form);
      btnBayar.disabled = true;
      btnBayar.textContent = 'Memproses transaksi...';

      try {
        const resp = await fetch(form.action, {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await resp.json();

        if (!data.ok || !data.snap_token) {
          alertBox.textContent = data.message || 'Gagal memulai pembayaran.';
          alertBox.classList.remove('d-none');
          btnBayar.disabled = false;
          btnBayar.textContent = 'Bayar & Kirim Suara via Midtrans';
          return;
        }

        window.snap.pay(data.snap_token, {
          onSuccess: function() {
            window.location.reload();
          },
          onPending: function() {
            window.location.reload();
          },
          onError: function() {
            alertBox.textContent = 'Pembayaran gagal diproses.';
            alertBox.classList.remove('d-none');
            btnBayar.disabled = false;
            btnBayar.textContent = 'Bayar & Kirim Suara via Midtrans';
          },
          onClose: function() {
            btnBayar.disabled = false;
            btnBayar.textContent = 'Bayar & Kirim Suara via Midtrans';
          }
        });
      } catch (err) {
        alertBox.textContent = 'Terjadi kesalahan jaringan saat menghubungi server.';
        alertBox.classList.remove('d-none');
        btnBayar.disabled = false;
        btnBayar.textContent = 'Bayar & Kirim Suara via Midtrans';
      }
    });
  }
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>