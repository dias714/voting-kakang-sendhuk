-- Database: db_voting_kakang_senduk
-- Aplikasi Voting Pemilihan Duta Wisata Kakang Senduk Ponorogo
-- Kompatibel dengan Laragon / XAMPP (MySQL 8 / MariaDB)

CREATE DATABASE IF NOT EXISTS db_voting_kakang_senduk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_voting_kakang_senduk;

-- 1. Tabel users
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  no_hp VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','pemilih') NOT NULL DEFAULT 'pemilih',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel kontestan
CREATE TABLE IF NOT EXISTS kontestan (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_peserta VARCHAR(10) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  kategori ENUM('Kakang','Senduk') NOT NULL,
  foto VARCHAR(255) NULL,
  asal_instansi VARCHAR(150) NULL,
  ttl VARCHAR(100) NULL,
  visi TEXT NULL,
  misi TEXT NULL,
  prestasi TEXT NULL,
  deskripsi TEXT NULL,
  UNIQUE KEY uq_nomor_kategori (nomor_peserta, kategori)
) ENGINE=InnoDB;

-- 3. Tabel periode_voting
CREATE TABLE IF NOT EXISTS periode_voting (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_periode VARCHAR(100) NOT NULL,
  tanggal_mulai DATETIME NOT NULL,
  tanggal_selesai DATETIME NOT NULL,
  status ENUM('Aktif','Nonaktif') NOT NULL DEFAULT 'Nonaktif'
) ENGINE=InnoDB;

-- 4. Tabel voting
CREATE TABLE IF NOT EXISTS voting (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  kontestan_id INT UNSIGNED NOT NULL,
  kategori ENUM('Kakang','Senduk') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_kategori (user_id, kategori),
  CONSTRAINT fk_voting_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_voting_kontestan FOREIGN KEY (kontestan_id) REFERENCES kontestan(id) ON DELETE CASCADE,
  INDEX idx_voting_kategori (kategori)
) ENGINE=InnoDB;

-- ==========================================================
-- DATA AWAL (SEEDS)
-- Akun Admin:
--   Email: admin@kakangsenduk.local | Password: password123
-- Akun Pemilih Demo:
--   Email: pemilih@kakangsenduk.local | Password: password123
-- ==========================================================

-- Hash password123: $2y$10$w8uQeY3Yq6Q1JvA5d9m5heBwPzM1J3ZkWwX5L6E0FqG7y0d2m0SLe
-- (atau dihasilkan via password_hash)
INSERT INTO users (id, nama, email, no_hp, password, role) VALUES
(1, 'Administrator Kakang Senduk', 'admin@kakangsenduk.local', '081234567890', '$2y$10$w8uQeY3Yq6Q1JvA5d9m5heBwPzM1J3ZkWwX5L6E0FqG7y0d2m0SLe', 'admin'),
(2, 'Ahmad Fauzi (Pemilih 1)', 'pemilih@kakangsenduk.local', '082198765432', '$2y$10$w8uQeY3Yq6Q1JvA5d9m5heBwPzM1J3ZkWwX5L6E0FqG7y0d2m0SLe', 'pemilih'),
(3, 'Dewi Lestari (Pemilih 2)', 'dewi@gmail.com', '085712349876', '$2y$10$w8uQeY3Yq6Q1JvA5d9m5heBwPzM1J3ZkWwX5L6E0FqG7y0d2m0SLe', 'pemilih'),
(4, 'Bagus Prasetyo (Pemilih 3)', 'bagus@gmail.com', '081345678912', '$2y$10$w8uQeY3Yq6Q1JvA5d9m5heBwPzM1J3ZkWwX5L6E0FqG7y0d2m0SLe', 'pemilih')
ON DUPLICATE KEY UPDATE nama=VALUES(nama);

-- Periode Pemilihan Aktif
INSERT INTO periode_voting (id, nama_periode, tanggal_mulai, tanggal_selesai, status) VALUES
(1, 'Pemilihan Duta Wisata Kakang Senduk Ponorogo 2026', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'Aktif')
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- Data 6 Finalis (3 Kakang, 3 Senduk)
INSERT INTO kontestan (id, nomor_peserta, nama, kategori, foto, asal_instansi, ttl, visi, misi, prestasi, deskripsi) VALUES
(1, '01', 'Kakang Ghazi Al-Fatih', 'Kakang', 'assets/img/kakang-avatar.svg', 'Universitas Brawijaya / Ponorogo', 'Ponorogo, 14 Mei 2004', 
 'Menjadikan kebudayaan Reyog Ponorogo dan pesona wisata lokal sebagai magnet pariwisata berkelas dunia melalui transformasi digital dan literasi budaya generasi muda.',
 '1. Mempromosikan destinasi wisata Ponorogo melalui kampanye media digital interaktif.\n2. Mengedukasi pelajar mengenai kelestarian budaya adiluhung Reyog.\n3. Membangun kolaborasi bersama UMKM ekonomi kreatif lokal.',
 'Juara 1 Duta Bahasa Jawa Timur 2025, Penerima Beasiswa Prestasi Seni Budaya',
 'Ghazi adalah pemuda berdedikasi tinggi asal Jetis Ponorogo yang aktif dalam riset pelestarian seni tradisional dan kepariwisataan alam Telaga Ngebel.'),

(2, '03', 'Kakang Elang Radhitya', 'Kakang', 'assets/img/kakang-avatar.svg', 'Universitas Gadjah Mada', 'Ponorogo, 22 September 2003',
 'Mewujudkan ekowisata Ponorogo yang berkelanjutan dan berdaya saing global dengan sinergi kearifan lokal.',
 '1. Menginisiasi program Green Tourism di kawasan wisata alam Ponorogo.\n2. Menjalin jejaring promosi pariwisata pemuda lintas provinsi.\n3. Mengembangkan konten storytelling sejarah Bumi Reog.',
 'Finalis Putra Putri Pariwisata Nusantara, Duta Kampus Berkelanjutan',
 'Elang memiliki kepedulian mendalam terhadap konservasi lingkungan dan pengembangan desa wisata di lereng pegunungan Ponorogo.'),

(3, '05', 'Kakang Dito Wicaksono', 'Kakang', 'assets/img/kakang-avatar.svg', 'SMKN 1 Ponorogo', 'Ponorogo, 10 Januari 2007',
 'Mendorong generasi Z untuk bangga menjadi duta promosi pariwisata daerah di kancah nasional.',
 '1. Membina duta wisata cilik di sekolah-sekolah se-Kabupaten Ponorogo.\n2. Memanfaatkan teknologi augmented reality dan multimedia untuk memperkenalkan budaya Reyog.\n3. Menguatkan jejaring komunitas seni dan sanggar reog.',
 'Juara 1 Lomba Cipta Kreasi Seni Daerah Ponorogo 2025, Juara Umum Debat Pelajar',
 'Dito merupakan siswa berprestasi yang giat memadukan keterampilan teknologi informasi dengan kecintaan pada seni reog Ponorogo.'),

(4, '02', 'Senduk Fahriya Ayu Kusuma', 'Senduk', 'assets/img/senduk-avatar.svg', 'Universitas Airlangga', 'Ponorogo, 18 Agustus 2004',
 'Menjadi representasi perempuan muda Ponorogo yang cerdas, berkarakter, anggun, dan visioner dalam memajukan citra pariwisata Ponorogo.',
 '1. Mengembangkan platform informasi wisata ramah keluarga di Ponorogo.\n2. Menggagas workshop pemberdayaan wanita pengrajin suvenir Reyog dan batik Ponorogan.\n3. Memperkenalkan kuliner khas seperti Sate Ponorogo dan Dawet Jabung ke pentas kuliner nusantara.',
 'Top 5 Puteri Remaja Jawa Timur 2024, Best Presentation Eco-Tourism Forum',
 'Fahriya menaruh perhatian besar pada diplomasi budaya, gastronomi khas Ponorogo, dan peningkatan kapasitas perajin batik lokal.'),

(5, '04', 'Senduk Olivia Maharani', 'Senduk', 'assets/img/senduk-avatar.svg', 'SMA Negeri 1 Ponorogo', 'Ponorogo, 05 Juli 2006',
 'Membangun sinergi pariwisata edukatif berbasis warisan budaya intangible Reyog Ponorogo untuk generasi masa depan.',
 '1. Melaksanakan program Senduk Menyapa di sekolah dan panti asuhan.\n2. Mempromosikan event akbar Grebeg Suro ke audiens wisatawan mancanegara.\n3. Mengampanyekan keramahtamahan (hospitality) masyarakat Ponorogo.',
 'Juara 1 Karya Tulis Ilmiah Cagar Budaya, Duta Genre Ponorogo',
 'Olivia adalah siswi berprestasi dengan kefasihan berbahasa asing dan aktif dalam riset budaya Grebeg Suro.'),

(6, '06', 'Senduk Kayla Zahrani', 'Senduk', 'assets/img/senduk-avatar.svg', 'SMKN 2 Ponorogo', 'Ponorogo, 12 Desember 2006',
 'Mengakselerasi potensi ekonomi kreatif generasi muda Ponorogo melalui promosi destinasi tersembunyi.',
 '1. Pemetaan dan publikasi hidden gems wisata alam dan air terjun di Ponorogo.\n2. Pelatihan pemandu wisata muda berbasis komunitas lokal.\n3. Kolaborasi aktif dengan asosiasi perhotelan dan kuliner Ponorogo.',
 'Juara 2 Tour Guide Contest Tingkat Pelajar, Duta Lingkungan Hidup Ponorogo',
 'Kayla memiliki ketertarikan tinggi pada pemanduan wisata minat khusus dan promosi kuliner otentik Ponorogo.')
ON DUPLICATE KEY UPDATE nama=VALUES(nama);

-- Data Simulasi Suara Awal (sehingga klasemen & chart langsung terlihat hidup)
INSERT INTO voting (user_id, kontestan_id, kategori, created_at) VALUES
(3, 1, 'Kakang', NOW() - INTERVAL 2 HOUR),
(4, 1, 'Kakang', NOW() - INTERVAL 1 HOUR),
(3, 4, 'Senduk', NOW() - INTERVAL 2 HOUR),
(4, 4, 'Senduk', NOW() - INTERVAL 50 MINUTE)
ON DUPLICATE KEY UPDATE created_at=VALUES(created_at);
