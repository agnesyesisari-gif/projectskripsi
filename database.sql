-- ============================================================
--  GKJ PENARUBAN - Database Setup
--  Sistem Informasi Kegiatan Pelayanan Gereja
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gkj_penaruban`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `gkj_penaruban`;

-- ── users ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama`       VARCHAR(100) NOT NULL,
    `username`   VARCHAR(50)  NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('admin','petugas') DEFAULT 'petugas',
    `status`     TINYINT(1) DEFAULT 1,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username=admin, password=admin123
INSERT INTO `users` (`nama`,`username`,`password`,`role`,`status`,`created_at`) VALUES
('Administrator','admin','$2y$12$4BgQCuFDQLVDxq1Y7sHGFe0pKlfEuMJlF7Ru.3oNiJRZJHQ5K3k8q','admin',1,NOW());

-- ── komisi ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `komisi` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama_komisi` VARCHAR(100) NOT NULL,
    `ketua`       VARCHAR(100) NULL,
    `deskripsi`   TEXT NULL,
    `created_at`  DATETIME NULL,
    `updated_at`  DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `komisi` (`nama_komisi`,`ketua`,`created_at`) VALUES
('Komisi Anak',    '-', NOW()),
('Komisi Pemuda',  '-', NOW()),
('Komisi Wanita',  '-', NOW()),
('Komisi Pria',    '-', NOW()),
('Komisi Lansia',  '-', NOW()),
('Komisi Diakonia','-', NOW());

-- ── jadwal_ibadah ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `jadwal_ibadah` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama_ibadah` VARCHAR(150) NOT NULL,
    `tanggal`     DATE NOT NULL,
    `jam`         TIME NOT NULL,
    `lokasi`      VARCHAR(200) NOT NULL,
    `petugas`     VARCHAR(150) NULL,
    `komisi_id`   INT UNSIGNED NULL,
    `keterangan`  TEXT NULL,
    `created_at`  DATETIME NULL,
    `updated_at`  DATETIME NULL,
    FOREIGN KEY (`komisi_id`) REFERENCES `komisi`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data jadwal
INSERT INTO `jadwal_ibadah` (`nama_ibadah`,`tanggal`,`jam`,`lokasi`,`petugas`,`created_at`) VALUES
('Ibadah Minggu Pagi', CURDATE(), '07:00:00', 'Gedung Gereja GKJ Penaruban', 'Pdt. Yohanes', NOW()),
('Ibadah Minggu Sore', CURDATE(), '17:00:00', 'Gedung Gereja GKJ Penaruban', 'Pdt. Maria',   NOW());

-- ── program_kerja ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `program_kerja` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama_program` VARCHAR(200) NOT NULL,
    `komisi_id`    INT UNSIGNED NULL,
    `bulan`        TINYINT UNSIGNED DEFAULT 1,
    `tahun`        SMALLINT UNSIGNED NOT NULL,
    `anggaran`     DECIMAL(15,2) NULL,
    `status`       ENUM('rencana','berjalan','selesai','batal') DEFAULT 'rencana',
    `keterangan`   TEXT NULL,
    `created_at`   DATETIME NULL,
    `updated_at`   DATETIME NULL,
    FOREIGN KEY (`komisi_id`) REFERENCES `komisi`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── pengumuman ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pengumuman` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `judul`          VARCHAR(200) NOT NULL,
    `isi`            TEXT NOT NULL,
    `tanggal_tayang` DATE NULL,
    `status`         ENUM('draft','aktif','nonaktif') DEFAULT 'draft',
    `created_by`     INT UNSIGNED NULL,
    `created_at`     DATETIME NULL,
    `updated_at`     DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── wa_log ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `wa_log` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nomor`      VARCHAR(20) NOT NULL,
    `pesan`      TEXT NOT NULL,
    `status`     ENUM('terkirim','gagal') DEFAULT 'terkirim',
    `created_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── ci_sessions ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ci_sessions` (
    `id`         VARCHAR(128) NOT NULL,
    `ip_address` VARCHAR(45)  NOT NULL,
    `timestamp`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    `data`       BLOB NOT NULL,
    KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
