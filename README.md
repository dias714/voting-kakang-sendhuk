# Voting Pemilihan Kakang Senduk Ponorogo

Aplikasi PHP Native + PDO + MySQL sesuai rancangan proyek.

## Instalasi
1. Ekstrak folder `voting-kakang-senduk` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka phpMyAdmin, impor file `database.sql`.
4. Periksa `config/koneksi.php`. Default XAMPP: host `localhost`, user `root`, password kosong.
5. Buka `http://localhost/voting-kakang-senduk/setup-admin.php` untuk membuat akun administrator awal.
6. Login melalui `http://localhost/voting-kakang-senduk/auth/login.php`.

## Akun admin awal
Email: `admin@kakangsenduk.local`
Password: `Admin123!`

Segera ubah password setelah login dan hapus `setup-admin.php` setelah akun admin berhasil dibuat.

## Fitur
- Beranda, daftar dan detail kontestan.
- Registrasi/login pemilih dengan `password_hash()` dan `password_verify()`.
- Voting satu suara per kategori per akun (Kakang dan Senduk).
- Validasi periode voting aktif dan proteksi unique key database.
- Klasemen terpisah, ranking, jumlah suara dan persentase.
- Dashboard admin, CRUD kontestan + upload foto, daftar pemilih, periode, hasil voting.

## Catatan
- Folder `uploads/` harus dapat ditulisi oleh Apache.
- Foto kontestan JPG/JPEG/PNG/WEBP maksimal 3 MB.
- Aplikasi ini merupakan fondasi proyek sekolah. Sebelum digunakan untuk pemilihan resmi, lakukan audit keamanan, backup, uji beban, dan verifikasi identitas pemilih.
