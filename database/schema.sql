-- Skema database Ave Mujica Fan Web v2.0
-- Kompatibel dengan database v1 ("avem"): tabel & kolom sama, jadi akun dan komentar lama tetap bisa dipakai.
--
-- Lokal (XAMPP): import file ini di phpMyAdmin, otomatis membuat database `avem`.
-- Hosting: buat database lewat panel hosting dulu, pilih database itu di phpMyAdmin,
--          lalu import file ini. Baris CREATE DATABASE / USE boleh dihapus kalau ditolak hosting.

CREATE DATABASE IF NOT EXISTS `avem` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `avem`;

CREATE TABLE IF NOT EXISTS `akun` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- page_id: 0 = beranda, 1 = Uika, 2 = Mutsumi, 3 = Umiri, 4 = Nyamu, 5 = Sakiko (lihat data/members.php)
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `page_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_page` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
