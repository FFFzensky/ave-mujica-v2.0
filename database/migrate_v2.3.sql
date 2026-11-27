-- Migrasi v2.2 -> v2.3 (jalankan SEKALI di phpMyAdmin pada database yang sudah ada).
-- Menambah kode pemulihan (lupa password) dan penanda password sementara.

ALTER TABLE `akun`
  ADD COLUMN `recovery_hash` VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN `must_change_password` TINYINT(1) NOT NULL DEFAULT 0;
