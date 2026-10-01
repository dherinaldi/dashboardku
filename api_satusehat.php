<?php
// API read-only: dashboard pengiriman SATUSEHAT (memanggil stored procedure dashboardPengiriman)
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
date_default_timezone_set('Asia/Jakarta');

if (!$koneksi) {
    error_log(mysqli_connect_error());
    http_response_code(500);
    echo json_encode(['error' => 'Gagal terhubung ke database.']);
    exit;
}

// Validasi tanggal (format YYYY-MM-DD), default hari ini
function tanggalValid($s)
{
    $d = DateTime::createFromFormat('Y-m-d', (string) $s);
    return ($d && $d->format('Y-m-d') === $s) ? $s : null;
}

$awal  = tanggalValid($_GET['awal'] ?? '') ?? date('Y-m-d');
$akhir = tanggalValid($_GET['akhir'] ?? '') ?? date('Y-m-d');
if ($awal > $akhir) {
    [$awal, $akhir] = [$akhir, $awal];
}

$pAwal  = $awal . ' 00:00:00';
$pAkhir = $akhir . ' 23:59:59';

// Parameter dikirim lewat prepared statement agar aman
$stmt = mysqli_prepare($koneksi, 'CALL `kemkes-ihs`.dashboardPengiriman_modif(?, ?)');
if (!$stmt) {
    error_log(mysqli_error($koneksi));
    http_response_code(500);
    echo json_encode(['error' => 'Stored procedure tidak dapat dipanggil.']);
    exit;
}
mysqli_stmt_bind_param($stmt, 'ss', $pAwal, $pAkhir);

if (!mysqli_stmt_execute($stmt)) {
    error_log(mysqli_stmt_error($stmt));
    http_response_code(500);
    echo json_encode(['error' => 'Query dashboard pengiriman gagal.']);
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = [
        'nama'   => $r['NAMA'],
        'terkirim' => (int) $r['MEMILIKI_ID'],       // NULL -> 0
        'belum'    => (int) $r['TDK_MEMILIKI_ID'],
        'total'    => (int) $r['TOTAL'],
    ];
}
mysqli_free_result($result);

// CALL menghasilkan result set tambahan, bersihkan sebelum menutup
while (mysqli_stmt_more_results($stmt) && mysqli_stmt_next_result($stmt)) {
}
mysqli_stmt_close($stmt);
mysqli_close($koneksi);

echo json_encode([
    'awal'    => $awal,
    'akhir'   => $akhir,
    'updated' => date('Y-m-d H:i:s'),
    'data'    => $rows,
]);
