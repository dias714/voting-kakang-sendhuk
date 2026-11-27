<?php
require_once __DIR__ . '/koneksi.php';

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function base_url(string $path = ''): string {
    $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/\\');
    if ($base === '') $base = '';
    return $base . '/' . ltrim($path, '/');
}
function redirect(string $path): never { header('Location: ' . base_url($path)); exit; }
function user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void { if (!user()) redirect('auth/login.php'); }
function require_admin(): void { require_login(); if (user()['role'] !== 'admin') { http_response_code(403); exit('Akses ditolak.'); } }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = [$message, $type]; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        [$m,$t] = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($t) . '">' . e($m) . '</div>';
    }
}
function active_period(PDO $pdo): ?array {
    $q = $pdo->query("SELECT * FROM periode_voting WHERE status='Aktif' AND NOW() BETWEEN tanggal_mulai AND tanggal_selesai ORDER BY id DESC LIMIT 1");
    return $q->fetch() ?: null;
}
function has_voted(PDO $pdo, int $uid, string $category): bool {
    $q = $pdo->prepare('SELECT id FROM voting WHERE user_id=? AND kategori=? LIMIT 1');
    $q->execute([$uid, $category]); return (bool)$q->fetch();
}
