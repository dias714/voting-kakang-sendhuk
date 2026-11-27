<?php
require_once __DIR__.'/../config/fungsi.php';require_login();verify_csrf();
if(user()['role']!=='pemilih')redirect('admin/dashboard.php');
try{$pdo->beginTransaction();$period=active_period($pdo);if(!$period)throw new RuntimeException('Periode voting tidak aktif.');
$q=$pdo->prepare('SELECT id,kategori FROM kontestan WHERE id=? FOR UPDATE');$q->execute([(int)($_POST['kontestan_id']??0)]);$k=$q->fetch();if(!$k)throw new RuntimeException('Kontestan tidak ditemukan.');
$q=$pdo->prepare('SELECT id FROM voting WHERE user_id=? AND kategori=?');$q->execute([user()['id'],$k['kategori']]);if($q->fetch())throw new RuntimeException('Kamu sudah memilih pada kategori '.$k['kategori'].'.');
$q=$pdo->prepare('INSERT INTO voting(user_id,kontestan_id,kategori,created_at) VALUES(?,?,?,NOW())');$q->execute([user()['id'],$k['id'],$k['kategori']]);$pdo->commit();flash('Voting berhasil disimpan. Terima kasih sudah berpartisipasi.');
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash($e->getMessage(),'danger');}
redirect('pages/voting.php');
