<?php
require_once __DIR__ . '/../config/fungsi.php';
if(user()) redirect(user()['role']==='admin'?'admin/dashboard.php':'pages/index.php');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$q=$pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');$q->execute([trim($_POST['email']??'')]);$u=$q->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password'])){session_regenerate_id(true);$_SESSION['user']=['id'=>$u['id'],'nama'=>$u['nama'],'email'=>$u['email'],'role'=>$u['role']];redirect($u['role']==='admin'?'admin/dashboard.php':'pages/index.php');}
 flash('Email atau password salah.','danger');
}
$page_title='Login';require __DIR__.'/../includes/header.php';?>
<div class="row justify-content-center"><div class="col-lg-5"><div class="panel"><div class="eyebrow">Selamat datang kembali</div><h1 class="section-title mb-4">Masuk</h1><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" required><button class="btn btn-gold w-100">Masuk</button></form><p class="mt-3 mb-0">Belum punya akun? <a href="register.php">Daftar</a></p></div></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>