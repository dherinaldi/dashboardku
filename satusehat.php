<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pengiriman SATUSEHAT</title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/vendor/poppins/poppins.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<header class="hero hero-satusehat text-white">
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                <div>
                    <h1 class="h3 fw-bold mb-0">Dashboard Pengiriman SATUSEHAT</h1>
                    <p class="mb-0 opacity-75">Monitoring resource yang sudah memiliki ID SATUSEHAT</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-hospital"></i> Dashboard Tempat Tidur
                </a>
                <div class="text-end">
                    <div class="fs-4 fw-semibold" id="clock">--:--:--</div>
                    <div class="small opacity-75" id="date">-</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-collection"></i></div>
                    <div><div class="stat-label">Total Data</div><div class="stat-value" id="sumTotal">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></div>
                    <div><div class="stat-label">Terkirim (Memiliki ID)</div><div class="stat-value text-success" id="sumTerkirim">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i></div>
                    <div><div class="stat-label">Belum Terkirim</div><div class="stat-value text-danger" id="sumBelum">0</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-speedometer2"></i></div>
                    <div><div class="stat-label">Persentase Terkirim</div><div class="stat-value" id="sumPct">0%</div></div>
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
            <button type="submit" class="btn btn-success"><i class="bi bi-funnel"></i> Tampilkan</button>
            <div class="btn-group" role="group" aria-label="Rentang cepat">
                <button type="button" class="btn btn-outline-secondary" data-range="0">Hari Ini</button>
                <button type="button" class="btn btn-outline-secondary" data-range="6">7 Hari</button>
                <button type="button" class="btn btn-outline-secondary" data-range="month">Bulan Ini</button>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="input-group search-box">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <label for="search" class="visually-hidden">Cari resource</label>
                <input type="search" id="search" class="form-control" placeholder="Cari resource...">
            </div>
            <div class="btn-group" role="group" aria-label="Mode tampilan">
                <button type="button" class="btn btn-outline-secondary active" data-view="card" aria-label="Tampilan kartu"><i class="bi bi-grid"></i></button>
                <button type="button" class="btn btn-outline-secondary" data-view="table" aria-label="Tampilan tabel"><i class="bi bi-list-ul"></i></button>
            </div>
        </div>
    </form>

    <div class="small text-muted mb-3">
        <i class="bi bi-info-circle"></i> Periode: <b id="periode">-</b> &middot;
        <i class="bi bi-arrow-repeat"></i> Update: <span id="lastUpdate">-</span> &middot;
        Resource master (Organization, Location, Patient, Practitioner) dihitung tanpa filter tanggal.
    </div>

    <div id="alertBox" role="alert" aria-live="polite"></div>

    <!-- Tampilan kartu -->
    <div class="row g-3" id="cardView">
        <div class="col-12 text-center text-muted py-5">
            <div class="spinner-border text-success" role="status"></div>
            <div class="mt-2">Memuat data...</div>
        </div>
    </div>

    <!-- Tampilan tabel -->
    <div class="card border-0 shadow-sm d-none" id="tableView">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th scope="col" class="text-center">No</th>
                    <th scope="col">Resource</th>
                    <th scope="col" class="text-center">Memiliki ID</th>
                    <th scope="col" class="text-center">Tidak Memiliki ID</th>
                    <th scope="col" class="text-center">Total</th>
                    <th scope="col" style="min-width:200px">Terkirim</th>
                </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>
    </div>

    <!-- Legenda -->
    <div class="d-flex flex-wrap gap-3 justify-content-center mt-4 small text-muted">
        <span><span class="legend bg-success"></span> Baik (&ge; 95%)</span>
        <span><span class="legend bg-warning"></span> Perlu Perhatian (80–94%)</span>
        <span><span class="legend bg-danger"></span> Kritis (&lt; 80%)</span>
        <span><span class="legend bg-secondary"></span> Tidak Ada Data</span>
    </div>
</main>

<footer class="text-center text-muted small py-3">
    &copy; <?= date('Y') ?> Dashboard SATUSEHAT
</footer>

<script src="assets/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/satusehat.js"></script>
</body>
</html>
