<?php
// API read-only: jadwal dokter HFIS sesuai HARI ini
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

// Kode hari HFIS: 1 = Senin ... 7 = Minggu (sama dengan date('N') di PHP)
$hari = (int) date('N');
$namaHari = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$hari];

$sql = "SELECT dhfis.KD_DOKTER, dhfis.NM_DOKTER, dhfis.KD_POLI, bpjs.NMPOLI as NM_POLI ,dhfis.KD_SUB_SPESIALIS, dhfis.NM_HARI, dhfis.JAM,
               dhfis.JAM_MULAI, dhfis.JAM_SELESAI, dhfis.KAPASITAS, dhfis.KOUTA_JKN, dhfis.KOUTA_NON_JKN, dhfis.LIBUR
        FROM regonline.jadwal_dokter_hfis dhfis
				LEFT JOIN regonline.poli_bpjs bpjs on dhfis.KD_POLI = bpjs.KDPOLI AND bpjs.ANTRIAN = 'A'								
        WHERE dhfis.HARI = $hari AND dhfis.STATUS = 1
				GROUP BY dhfis.KD_DOKTER
        ORDER BY dhfis.KD_POLI, dhfis.JAM_MULAI, dhfis.NM_DOKTER
				;";

$query = mysqli_query($koneksi, $sql);
if (!$query) {
    error_log(mysqli_error($koneksi));
    http_response_code(500);
    echo json_encode(['error' => 'Query jadwal dokter gagal.']);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($query)) {
    $rows[] = [
        'kd_dokter'     => $r['KD_DOKTER'],
        'nm_dokter'     => trim($r['NM_DOKTER']),
        'kd_poli'       => trim($r['KD_POLI']),
        'nm_poli'       => trim($r['NM_POLI']),
        'sub_spesialis' => trim($r['KD_SUB_SPESIALIS']),
        'jam'           => trim($r['JAM']),
        'jam_mulai'     => $r['JAM_MULAI'] ? substr($r['JAM_MULAI'], 0, 5) : null,
        'jam_selesai'   => $r['JAM_SELESAI'] ? substr($r['JAM_SELESAI'], 0, 5) : null,
        'kapasitas'     => (int) $r['KAPASITAS'],
        'kuota_jkn'     => (int) $r['KOUTA_JKN'],
        'kuota_non_jkn' => (int) $r['KOUTA_NON_JKN'],
        'libur'         => (int) $r['LIBUR'] === 1,
    ];
}
mysqli_free_result($query);
mysqli_close($koneksi);

echo json_encode([
    'hari'    => $namaHari,
    'tanggal' => date('Y-m-d'),
    'data'    => $rows,
]);
