<?php
// API read-only: rekap capaian antrian online BPJS (SP laporan.RekapPersentaseAntrianBPJS)
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

// Validasi tanggal (YYYY-MM-DD), default: awal bulan ini s/d hari ini
function tanggalValid($s)
{
    $d = DateTime::createFromFormat('Y-m-d', (string) $s);
    return ($d && $d->format('Y-m-d') === $s) ? $s : null;
}

$awal  = tanggalValid($_GET['awal'] ?? '') ?? date('Y-m-01');
$akhir = tanggalValid($_GET['akhir'] ?? '') ?? date('Y-m-d');
if ($awal > $akhir) {
    [$awal, $akhir] = [$akhir, $awal];
}

$pAwal  = $awal . ' 00:00:00';
$pAkhir = $akhir . ' 23:59:59';

$stmt = mysqli_prepare($koneksi, 'CALL laporan.RekapPersentaseAntrianBPJS(?, ?)');
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
    echo json_encode(['error' => 'Query rekap antrian gagal.']);
    exit;
}

$result = mysqli_stmt_get_result($stmt);

// Pisahkan menjadi BULANAN & HARIAN
$bulanan = [];
$harian  = [];
$waktuCetak = null;

while ($r = mysqli_fetch_assoc($result)) {
    $row = [
        'periode'      => $r['PERIODE'],
        'vclaim'       => (int) $r['VCLAIM'],
        'simgos'       => (int) $r['SIMGOS'],
        'jmlsep'       => (int) $r['JMLSEP'],
        'antrian'      => (int) $r['ANTRIAN'],
        'mjkn'         => (int) $r['MJKN'],
        'rs'           => (int) $r['RS'],
        'batal'        => (int) $r['BATAL'],
        'blmlyn'       => (int) $r['BLMLYN'],
        'sdglyn'       => (int) $r['SDGLYN'],
        'slslyn'       => (int) $r['SLSLYN'],
        'capaian'      => (float) $r['CAPAIAN'],
        'potensi'      => (float) $r['POTENSI'],
        'persen_rs'    => (float) $r['PERSEN_RS'],
        'persen_mjkn'  => (float) $r['PERSEN_MJKN'],
    ];
    $waktuCetak = $r['WAKTU_CETAK'];
    if ($r['TIPE'] === 'BULANAN') {
        $bulanan[] = $row;
    } else {
        $harian[] = $row;
    }
}
mysqli_free_result($result);

// Bersihkan result set tambahan dari CALL
while (mysqli_stmt_more_results($stmt) && mysqli_stmt_next_result($stmt)) {
}
mysqli_stmt_close($stmt);
mysqli_close($koneksi);

echo json_encode([
    'awal'        => $awal,
    'akhir'       => $akhir,
    'waktu_cetak' => $waktuCetak,
    'updated'     => date('Y-m-d H:i:s'),
    'bulanan'     => $bulanan,
    'harian'      => $harian,
]);
