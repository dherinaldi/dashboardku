<?php
// =============================================================
// KONFIGURASI DATABASE
// =============================================================
// Salin file ini menjadi config.php lalu isi kredensial yang benar:
//   copy config.example.php config.php
//
// JANGAN commit config.php ke Git (sudah di .gitignore).
// =============================================================

const DB_HOST = 'localhost';         // IP / hostname server MySQL
const DB_NAME = 'informasi';        // Database default
const DB_USER = 'root';             // Username MySQL
const DB_PASS = '';                  // Password MySQL

// Matikan exception mysqli agar error bisa dicek manual dengan if
mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($koneksi) {
    mysqli_set_charset($koneksi, 'utf8mb4');
}
