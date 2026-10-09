<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TV Mode - Ketersediaan Tempat Tidur</title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/vendor/poppins/poppins.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<!-- Header -->
<header class="hero text-white">
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon"><i class="bi bi-hospital"></i></div>
                <div>
                    <h1 class="h3 fw-bold mb-0">Ketersediaan Tempat Tidur</h1>
                    <p class="mb-0 opacity-75">Informasi real-time ruang rawat inap</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge text-bg-light"><i class="bi bi-tv"></i> TV Mode</span>
                <div class="text-end">
                    <div class="fs-4 fw-semibold" id="clock">--:--:--</div>
                    <div class="small opacity-75" id="date">-</div>
                </div>
            </div>
        </div>

        <!-- Kartu ringkasan -->
        <div class="row g-3 mt-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-grid-3x3-gap"></i></div>
                    <div><div class="stat-label">Total Tempat Tidur</div><div class="stat-value" id="sumTotal">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-person-fill"></i></div>
                    <div><div class="stat-label">Terisi</div><div class="stat-value text-danger" id="sumTerisi">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></div>
                    <div><div class="stat-label">Kosong</div><div class="stat-value text-success" id="sumKosong">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-pie-chart-fill"></i></div>
                    <div><div class="stat-label">Tingkat Hunian (BOR)</div><div class="stat-value" id="sumBor">0%</div></div>
                </div>
            </div>
        </div>
    </div>
</header>


<main class="container my-4">
    <div id="alertBox" role="alert" aria-live="polite"></div>

    <!-- Tampilan kartu (TV selalu kartu) -->
    <div class="row g-3" id="cardView">
        <div class="col-12 text-center text-muted py-5">
            <div class="spinner-border text-success" role="status"></div>
            <div class="mt-2">Memuat data...</div>
        </div>
    </div>

    <!-- Legenda -->
    <div class="d-flex flex-wrap gap-3 justify-content-center mt-4 small text-muted">
        <span><span class="legend bg-success"></span> Tersedia (&lt; 50%)</span>
        <span><span class="legend bg-warning"></span> Hampir Penuh (50–79%)</span>
        <span><span class="legend bg-danger"></span> Penuh / Kritis (&ge; 80%)</span>
    </div>

    <!-- Jadwal Dokter HFIS -->
    <section class="mt-5" aria-labelledby="jadwalTitle">
        <div class="section-head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h4 fw-bold mb-0" id="jadwalTitle"><i class="bi bi-calendar2-week text-success"></i> Jadwal Dokter Hari Ini</h2>
                <div class="text-muted small"><span id="jadwalHari">-</span> &middot; <span id="jadwalCount">0 dokter</span></div>
            </div>
            <input type="hidden" id="searchDokter">
        </div>
        <div class="row g-3" id="jadwalView">
            <div class="col-12 text-center text-muted py-5">
                <div class="spinner-border text-success" role="status"></div>
                <div class="mt-2">Memuat jadwal...</div>
            </div>
        </div>
    </section>
</main>

<footer class="text-center text-muted small py-3">
    &copy; <?= date('Y') ?> Informasi Tempat Tidur
</footer>

<script src="assets/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
<script src="assets/jadwal.js"></script>
<script src="assets/autoscroll.js"></script>
<script src="assets/tv.js"></script>
</body>
</html>
