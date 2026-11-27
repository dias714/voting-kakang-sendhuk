<?php
require_once __DIR__ . '/../config/fungsi.php';
$page_title = 'Beranda · Kakang Senduk Ponorogo';
$nav_active = 'beranda';
$total = (int)$pdo->query('SELECT COUNT(*) FROM kontestan')->fetchColumn();
$period = active_period($pdo);
require __DIR__ . '/../includes/header.php';
?>
<section class="hero mb-5">
  <div>
    <div class="eyebrow mb-2">Duta Wisata Kabupaten Ponorogo</div>
    <h1>Kenali Potensi Daerah, <span class="gold">Dukung Generasi Terbaik.</span></h1>
    <p class="lead mt-3 mb-4">Berikan dukunganmu kepada finalis Kakang dan Senduk Ponorogo. Kenali profil, visi, dan misi setiap kontestan.</p>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-primary btn-lg" href="kontestan.php">Lihat Kontestan</a>
      <a class="btn btn-outline-light btn-lg" href="klasemen.php">Lihat Klasemen</a>
    </div>
  </div>
</section>

<div class="row g-3 g-md-4 mb-5">
  <div class="col-md-4">
    <div class="stat-card">
      <div class="eyebrow">Kontestan</div>
      <div class="stat-number"><?= $total ?></div>
      <div class="text-secondary small">Finalis terdaftar</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="eyebrow">Periode Voting</div>
      <div class="fw-bold fs-5 mt-1"><?= $period ? 'Sedang berlangsung' : 'Belum aktif' ?></div>
      <div class="text-secondary small mt-1"><?= $period ? e($period['nama_periode']) : 'Pantau informasi periode di sini' ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="eyebrow">Partisipasi</div>
      <div class="fw-bold fs-5 mt-1">Satu akun, dua kategori</div>
      <div class="text-secondary small mt-1">Satu suara Kakang dan satu Senduk</div>
    </div>
  </div>
</div>

<div class="panel text-center">
  <div class="eyebrow">Berpartisipasi</div>
  <h2 class="section-title my-2">Suaramu berarti untuk Ponorogo</h2>
  <p class="text-secondary mb-4 mx-auto" style="max-width:520px">Masuk atau daftar sebagai pemilih untuk memberikan dukungan resmi kepada finalis pilihanmu.</p>
  <a class="btn btn-primary btn-lg" href="<?= e(base_url(user() ? 'pages/voting.php' : 'auth/register.php')) ?>">Voting Sekarang</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
