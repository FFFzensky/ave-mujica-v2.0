-- Migrasi v2.1 -> v2.2 (jalankan SEKALI di phpMyAdmin pada database yang sudah ada).
-- Menambah: role admin, balasan komentar, dan pencatatan percobaan login.

ALTER TABLE `akun`
  ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'user';

ALTER TABLE `comments`
  ADD COLUMN `parent_id` INT NULL DEFAULT NULL,
  ADD COLUMN `reply_to` VARCHAR(50) NULL DEFAULT NULL,
  ADD KEY `idx_parent` (`parent_id`);

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `attempted_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jadikan akun pertama sebagai admin (ganti username kalau perlu).
UPDATE `akun` SET `role` = 'admin' WHERE `username` = 'adminumiri';
