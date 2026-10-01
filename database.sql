-- Database aplikasi Tempat Tidur (data dari tempat_tidur_kemkes.xls)
CREATE DATABASE IF NOT EXISTS pecut_tt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pecut_tt;

CREATE TABLE IF NOT EXISTS tempat_tidur (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_ruang  VARCHAR(100) NOT NULL,
    kelas       VARCHAR(20)  NOT NULL,
    terisi      INT UNSIGNED NOT NULL DEFAULT 0,
    kosong      INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO tempat_tidur (nama_ruang, kelas, terisi, kosong) VALUES
('anturium',  'IIA', 2, 3),
('flamboyan', 'III', 10, 10),
('ICU',       'I',   2, 3);
