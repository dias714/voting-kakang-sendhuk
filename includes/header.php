<?php require_once __DIR__ . '/../config/fungsi.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title ?? 'Kakang Senduk Ponorogo') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/style.css?v=2')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top topbar">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= e(base_url('pages/index.php')) ?>">
      KAKANG <span>SENDUK</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <div class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <a class="nav-link <?= ($nav_active ?? '') === 'kontestan' ? 'active' : '' ?>" href="<?= e(base_url('pages/kontestan.php')) ?>">Kontestan</a>
        <a class="nav-link <?= ($nav_active ?? '') === 'klasemen' ? 'active' : '' ?>" href="<?= e(base_url('pages/klasemen.php')) ?>">Klasemen</a>
        <?php if (user()): ?>
          <?php if (user()['role'] === 'admin'): ?>
            <a class="nav-link <?= ($nav_active ?? '') === 'admin' ? 'active' : '' ?>" href="<?= e(base_url('admin/dashboard.php')) ?>">Dashboard Admin</a>
          <?php else: ?>
            <a class="nav-link <?= ($nav_active ?? '') === 'voting' ? 'active' : '' ?>" href="<?= e(base_url('pages/voting.php')) ?>">Voting</a>
          <?php endif; ?>
          <span class="navbar-text text-secondary small mx-lg-2"><?= e(user()['nama']) ?></span>
          <a class="btn btn-outline-secondary btn-sm" href="<?= e(base_url('auth/logout.php')) ?>">Keluar</a>
        <?php else: ?>
          <a class="nav-link <?= ($nav_active ?? '') === 'login' ? 'active' : '' ?>" href="<?= e(base_url('auth/login.php')) ?>">Masuk</a>
          <a class="btn btn-primary btn-sm ms-lg-1" href="<?= e(base_url('auth/register.php')) ?>">Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="container py-4 py-md-5">
  <?php show_flash(); ?>
