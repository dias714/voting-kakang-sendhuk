<?php
require_once __DIR__ . '/../config/fungsi.php';
require_admin();
$stats = [
  'Kontestan' => (int)$pdo->query('SELECT COUNT(*) FROM kontestan')->fetchColumn(),
  'Pemilih' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='pemilih'")->fetchColumn(),
  'Suara Kakang' => (int)$pdo->query("SELECT COUNT(*) FROM voting WHERE kategori='Kakang'")->fetchColumn(),
  'Suara Senduk' => (int)$pdo->query("SELECT COUNT(*) FROM voting WHERE kategori='Senduk'")->fetchColumn(),
];
$period = active_period($pdo);
$page_title = 'Dashboard Admin';
$admin_page = 'dashboard.php';
require __DIR__ . '/_layout_top.php';
?>
<div class="eyebrow">Ringkasan sistem</div>
<h1 class="section-title mb-4">Dashboard Administrator</h1>

<div class="row g-3 g-md-4 mb-4">
  <?php foreach ($stats as $label => $num): ?>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card">
        <div class="text-secondary small fw-medium"><?= e($label) ?></div>
        <div class="stat-number"><?= $num ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="panel">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
    <h2 class="h6 fw-bold mb-0">Status Periode Voting</h2>
    <?php if ($period): ?>
      <span class="badge text-bg-success rounded-pill px-3 py-2">Aktif</span>
    <?php else: ?>
      <span class="badge text-bg-secondary rounded-pill px-3 py-2">Tidak ada periode aktif</span>
    <?php endif; ?>
  </div>
  <?php if ($period): ?>
    <p class="text-secondary mb-0">
      <strong class="text-dark"><?= e($period['nama_periode']) ?></strong> &middot;
      <?= e($period['tanggal_mulai']) ?> sampai <?= e($period['tanggal_selesai']) ?>
    </p>
  <?php else: ?>
    <p class="text-secondary small mb-0">Aktifkan periode voting pada menu Periode Voting agar pemilih dapat memberikan suara.</p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
