<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi Ketersediaan Tempat Tidur</title>
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
                <a href="satusehat.php" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-cloud-arrow-up"></i> Dashboard SATUSEHAT
                </a>
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
    <!-- Toolbar -->
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group search-box">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <label for="search" class="visually-hidden">Cari nama ruang</label>
                <input type="search" id="search" class="form-control" placeholder="Cari nama ruang...">
            </div>
            <label for="filterKelas" class="visually-hidden">Filter kelas</label>
            <select id="filterKelas" class="form-select w-auto">
                <option value="">Semua Kelas</option>
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted"><i class="bi bi-arrow-repeat"></i> Update: <span id="lastUpdate">-</span></span>
            <div class="btn-group" role="group" aria-label="Mode tampilan">
                <button class="btn btn-outline-secondary active" data-view="card" aria-label="Tampilan kartu"><i class="bi bi-grid"></i></button>
                <button class="btn btn-outline-secondary" data-view="table" aria-label="Tampilan tabel"><i class="bi bi-list-ul"></i></button>
            </div>
        </div>
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
                    <th scope="col">Nama Ruang</th>
                    <th scope="col" class="text-center">Kelas</th>
                    <th scope="col" class="text-center">Terisi</th>
                    <th scope="col" class="text-center">Kosong</th>
                    <th scope="col" class="text-center">Total</th>
                    <th scope="col" style="min-width:200px">Keterisian</th>
                </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
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
            <div class="input-group search-box">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <label for="searchDokter" class="visually-hidden">Cari dokter atau poli</label>
                <input type="search" id="searchDokter" class="form-control" placeholder="Cari dokter / poli...">
            </div>
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
</body>
</html>
