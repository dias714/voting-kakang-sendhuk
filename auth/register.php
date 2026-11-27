<?php
require_once __DIR__ . '/../config/fungsi.php';
if(user()) redirect('pages/index.php');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$nama=trim($_POST['nama']??'');$email=trim($_POST['email']??'');$hp=trim($_POST['no_hp']??'');$pw=$_POST['password']??'';
 if($nama===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pw)<8){flash('Lengkapi data dengan benar. Password minimal 8 karakter.','danger');}
 else {try{$q=$pdo->prepare("INSERT INTO users(nama,email,no_hp,password,role) VALUES(?,?,?,?, 'pemilih')");$q->execute([$nama,$email,$hp,password_hash($pw,PASSWORD_DEFAULT)]);flash('Registrasi berhasil. Silakan masuk.');redirect('auth/login.php');}catch(PDOException $e){flash('Email sudah terdaftar atau data tidak valid.','danger');}}
}
$page_title='Daftar Pemilih';require __DIR__.'/../includes/header.php';?>
<div class="row justify-content-center"><div class="col-lg-6"><div class="panel"><div class="eyebrow">Buat akun</div><h1 class="section-title mb-4">Registrasi Pemilih</h1><form method="post"><?= '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">' ?><label class="form-label">Nama lengkap</label><input class="form-control mb-3" name="nama" required><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Nomor HP</label><input class="form-control mb-3" name="no_hp" required><label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" minlength="8" required><button class="btn btn-gold w-100">Buat Akun</button></form><p class="mt-3 mb-0">Sudah punya akun? <a href="login.php">Masuk</a></p></div></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>