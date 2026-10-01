<?php
// Konfigurasi koneksi database (default Laragon: root tanpa password)
const DB_HOST = '192.168.100.170';
const DB_NAME = 'informasi';
const DB_USER = 'admin';
const DB_PASS = 'S!MRSGos2';

// Matikan exception mysqli agar error bisa dicek manual dengan if
mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($koneksi) {
    mysqli_set_charset($koneksi, 'utf8mb4');
}
