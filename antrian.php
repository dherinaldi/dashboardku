<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Capaian Antrian Online BPJS</title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/vendor/poppins/poppins.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<header class="hero hero-antrian text-white">
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <h1 class="h3 fw-bold mb-0">Capaian Antrian Online BPJS</h1>
                    <p class="mb-0 opacity-75">Rekapitulasi capaian antrian rawat jalan (BPJS)</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-hospital"></i> Tempat Tidur
                </a>
                <a href="satusehat.php" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-cloud-arrow-up"></i> SATUSEHAT
                </a>
                <div class="text-end">
                    <div class="fs-4 fw-semibold" id="clock">--:--:--</div>
                    <div class="small opacity-75" id="date">-</div>
                </div>
            </div>
        </div>

        <!-- Ringkasan (bulan berjalan) -->
        <div class="row g-3 mt-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-card-checklist"></i></div>
                    <div><div class="stat-label">Jumlah SEP</div><div class="stat-value" id="sumSep">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-list-ol"></i></div>
                    <div><div class="stat-label">Selesai Dilayani</div><div class="stat-value text-info" id="sumSelesai">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
                    <div><div class="stat-label">Capaian</div><div class="stat-value text-success" id="sumCapaian">0%</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-speedometer2"></i></div>
                    <div><div class="stat-label">Potensi</div><div class="stat-value" id="sumPotensi">0%</div></div>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="container my-4">
    <!-- Toolbar -->
    <form id="filterForm" class="d-flex flex-wrap gap-2 justify-content-between align-items-end mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label for="tglAwal" class="form-label small mb-1">Tanggal Awal</label>
                <input type="date" id="tglAwal" class="form-control" required>
            </div>
            <div>
                <label for="tglAkhir" class="form-label small mb-1">Tanggal Akhir</label>
                <input type="date" id="tglAkhir" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Tampilkan</button>
            <div class="btn-group" role="group" aria-label="Rentang cepat">
                <button type="button" class="btn btn-outline-secondary" data-range="0">Hari Ini</button>
                <button type="button" class="btn btn-outline-secondary" data-range="6">7 Hari</button>
                <button type="button" class="btn btn-outline-secondary" data-range="month">Bulan Ini</button>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted"><i class="bi bi-arrow-repeat"></i> Update: <span id="lastUpdate">-</span></span>
        </div>
    </form>

    <div class="small text-muted mb-3">
        <i class="bi bi-info-circle"></i> Periode: <b id="periode">-</b> &middot;
        Waktu Cetak: <span id="waktuCetak">-</span>
    </div>

    <div id="alertBox" role="alert" aria-live="polite"></div>

    <!-- Rekap Bulanan -->
    <section class="mb-4" aria-labelledby="bulananTitle">
        <div class="section-head section-head-antrian mb-3">
            <h2 class="h5 fw-bold mb-0" id="bulananTitle"><i class="bi bi-calendar-month"></i> Rekap Bulanan</h2>
        </div>
        <div class="card border-0 shadow-sm antrian-table">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Periode</th>
                        <th class="text-center">SEP VClaim</th>
                        <th class="text-center">SEP Bridging</th>
                        <th class="text-center">Jumlah SEP</th>
                        <th class="text-center">Antrian MJKN</th>
                        <th class="text-center">Antrian RS</th>
                        <th class="text-center">Jumlah Antrian</th>
                        <th class="text-center">Batal / Tdk Hadir</th>
                        <th class="text-center">Belum Dilayani</th>
                        <th class="text-center">Sedang Dilayani</th>
                        <th class="text-center">Selesai Dilayani</th>
                        <th class="text-center">Potensi</th>
                        <th class="text-center">Capaian</th>
                        <th class="text-center">% RS</th>
                        <th class="text-center">% MJKN</th>
                    </tr>
                    </thead>
                    <tbody id="tbodyBulanan"></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Rekap Harian -->
    <section aria-labelledby="harianTitle">
        <div class="section-head section-head-antrian mb-3">
            <h2 class="h5 fw-bold mb-0" id="harianTitle"><i class="bi bi-calendar-day"></i> Rekap Harian</h2>
        </div>
        <div class="card border-0 shadow-sm antrian-table">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Periode</th>
                        <th class="text-center">SEP VClaim</th>
                        <th class="text-center">SEP Bridging</th>
                        <th class="text-center">Jumlah SEP</th>
                        <th class="text-center">Antrian MJKN</th>
                        <th class="text-center">Antrian RS</th>
                        <th class="text-center">Jumlah Antrian</th>
                        <th class="text-center">Batal / Tdk Hadir</th>
                        <th class="text-center">Belum Dilayani</th>
                        <th class="text-center">Sedang Dilayani</th>
                        <th class="text-center">Selesai Dilayani</th>
                        <th class="text-center">Potensi</th>
                        <th class="text-center">Capaian</th>
                        <th class="text-center">% RS</th>
                        <th class="text-center">% MJKN</th>
                    </tr>
                    </thead>
                    <tbody id="tbodyHarian"></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Legenda -->
    <div class="d-flex flex-wrap gap-3 justify-content-center mt-4 small text-muted">
        <span><span class="legend bg-success"></span> Capaian Baik (&ge; 85%)</span>
        <span><span class="legend bg-warning"></span> Perlu Perhatian (70–84%)</span>
        <span><span class="legend bg-danger"></span> Rendah (&lt; 70%)</span>
    </div>
</main>

<footer class="text-center text-muted small py-3">
    &copy; <?= date('Y') ?> Capaian Antrian Online BPJS
</footer>

<script src="assets/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/antrian.js"></script>
</body>
</html>
