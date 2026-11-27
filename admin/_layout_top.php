<?php
require_once __DIR__ . '/../config/fungsi.php';
require_admin();
$page_title = $page_title ?? 'Admin';
$nav_active = 'admin';
$admin_page = $admin_page ?? basename($_SERVER['PHP_SELF']);
require __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-side">
    <div class="eyebrow px-2 pt-1 pb-2">Administrator</div>
    <a class="<?= $admin_page === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
    <a class="<?= $admin_page === 'kontestan.php' ? 'active' : '' ?>" href="kontestan.php">Data Kontestan</a>
    <a class="<?= $admin_page === 'pemilih.php' ? 'active' : '' ?>" href="pemilih.php">Data Pemilih</a>
    <a class="<?= $admin_page === 'periode.php' ? 'active' : '' ?>" href="periode.php">Periode Voting</a>
    <a class="<?= $admin_page === 'hasil-voting.php' ? 'active' : '' ?>" href="hasil-voting.php">Hasil Voting</a>
    <hr class="my-2 border-secondary-subtle">
    <a href="../pages/index.php">Lihat Website</a>
  </aside>
  <section>
