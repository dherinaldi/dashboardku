<?php
// Proxy sinkronisasi antrean BPJS: hit webservice getAntreanPerTanggal per tanggal
// (dipanggil dari browser agar tidak kena CORS lintas-origin)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
date_default_timezone_set('Asia/Jakarta');

const WS_BASE = 'http://192.168.100.170/webservice/registrasionline/bpjs/getAntreanPerTanggal';
const WS_TIMEOUT = 30;   // detik per tanggal
const MAX_HARI   = 62;   // batas jumlah tanggal agar tidak kelamaan

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

// Bangun daftar tanggal
$mulai = new DateTime($awal);
$selesai = new DateTime($akhir);
$selesai->modify('+1 day');
$periode = new DatePeriod($mulai, new DateInterval('P1D'), $selesai);

$tanggalList = [];
foreach ($periode as $d) {
    $tanggalList[] = $d->format('Y-m-d');
}

if (count($tanggalList) > MAX_HARI) {
    http_response_code(400);
    echo json_encode(['error' => 'Rentang terlalu panjang (maks ' . MAX_HARI . ' hari).']);
    exit;
}

// Hit webservice per tanggal
function hitWebservice($tanggal)
{
    $url = WS_BASE . '?loadData=1&tanggal=' . rawurlencode($tanggal);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => WS_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            return ['ok' => false, 'code' => $code, 'pesan' => $err ?: 'Koneksi gagal'];
        }
        return ['ok' => $code >= 200 && $code < 300, 'code' => $code, 'pesan' => substr(trim($body), 0, 300)];
    }

    // Fallback tanpa cURL
    $ctx = stream_context_create(['http' => ['timeout' => WS_TIMEOUT, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        return ['ok' => false, 'code' => 0, 'pesan' => 'Koneksi gagal (file_get_contents)'];
    }
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int) $m[1];
    }
    return ['ok' => $code === 0 || ($code >= 200 && $code < 300), 'code' => $code, 'pesan' => substr(trim($body), 0, 300)];
}

$hasil = [];
$sukses = 0;
foreach ($tanggalList as $tgl) {
    $r = hitWebservice($tgl);
    if ($r['ok']) {
        $sukses++;
    }
    $hasil[] = [
        'tanggal' => $tgl,
        'ok'      => $r['ok'],
        'code'    => $r['code'],
        'pesan'   => $r['pesan'],
    ];
}

echo json_encode([
    'awal'    => $awal,
    'akhir'   => $akhir,
    'total'   => count($tanggalList),
    'sukses'  => $sukses,
    'gagal'   => count($tanggalList) - $sukses,
    'updated' => date('Y-m-d H:i:s'),
    'detail'  => $hasil,
]);
