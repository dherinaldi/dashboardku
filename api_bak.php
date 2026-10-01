<?php
// API read-only: mengembalikan data tempat tidur dalam format JSON
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak diizinkan.']);
    exit;
}

if (!$koneksi) {
    error_log(mysqli_connect_error());
    http_response_code(500);
    echo json_encode(['error' => 'Gagal terhubung ke database. Periksa config.php.']);
    exit;
}

// Query langsung - sesuaikan nama tabel & kolom dengan database eksisting.
// Alias (AS ...) jangan diubah karena dipakai oleh assets/app.js
$sql = "SELECT nama_ruang AS nama_ruang,
               kelas      AS kelas,
               terisi     AS terisi,
               kosong     AS kosong
        FROM tempat_tidur
        ORDER BY nama_ruang";

$query = mysqli_query($koneksi, $sql);
if (!$query) {
    error_log(mysqli_error($koneksi));
    http_response_code(500);
    echo json_encode(['error' => 'Query gagal. Periksa nama tabel/kolom di api.php.']);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($query)) {
    $rows[] = [
        'nama_ruang' => $r['nama_ruang'],
        'kelas'      => $r['kelas'],
        'terisi'     => (int) $r['terisi'],
        'kosong'     => (int) $r['kosong'],
    ];
}
mysqli_free_result($query);
mysqli_close($koneksi);

echo json_encode(['data' => $rows, 'updated' => date('Y-m-d H:i:s')]);
