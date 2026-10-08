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
// TTLAKI/TTPEREMPUAN = kapasitas (total) TT; JMLLAKI/JMLPEREMPUAN = jumlah terisi.
$sql = "SELECT ttk.KAMAR, ttk.KELAS, ttk.JENISKAMAR,
               ttk.TTLAKI, ttk.TTPEREMPUAN, ttk.JMLLAKI, ttk.JMLPEREMPUAN,
               ttk.LASTUPDATED
        FROM informasi.tempat_tidur_kemkes ttk";

$query = mysqli_query($koneksi, $sql);
if (!$query) {
    error_log(mysqli_error($koneksi));
    http_response_code(500);
    echo json_encode(['error' => 'Query gagal. Periksa nama tabel/kolom di api.php.']);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($query)) {
    $terisi = (int) $r['JMLLAKI'] + (int) $r['JMLPEREMPUAN'];   // jumlah terisi L + P
    $total  = (int) $r['TTLAKI'] + (int) $r['TTPEREMPUAN'];     // kapasitas L + P
    $kosong = max(0, $total - $terisi);                        // sisa tempat tidur
    $rows[] = [
        'nama_ruang' => $r['KAMAR'],
        'kelas'      => $r['KELAS'],
        'terisi'     => $terisi,
        'kosong'     => $kosong,
    ];
}
mysqli_free_result($query);
mysqli_close($koneksi);

echo json_encode(['data' => $rows, 'updated' => date('Y-m-d H:i:s')]);
