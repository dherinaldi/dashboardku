# Dashboard Rumah Sakit

Dashboard read-only berbasis **PHP + jQuery + MySQL** (Bootstrap 5) untuk monitoring operasional rumah sakit. Terdiri dari tiga modul utama: **Ketersediaan Tempat Tidur**, **Jadwal Dokter HFIS**, dan **Dashboard Pengiriman SATUSEHAT**.

> **Catatan:** Aplikasi ini bersifat **read-only** — tidak ada fitur insert, update, atau delete. Semua data dibaca langsung dari database operasional yang sudah ada.

---

## Daftar Isi

- [Fitur](#fitur)
- [Struktur File](#struktur-file)
- [Prasyarat](#prasyarat)
- [Instalasi](#instalasi)
- [Konfigurasi Database](#konfigurasi-database)
- [Endpoint API](#endpoint-api)
- [Skema Database](#skema-database)
- [Vendor / Library](#vendor--library)
- [Catatan Teknis](#catatan-teknis)
- [Troubleshooting](#troubleshooting)

---

## Fitur

### 1. Dashboard Tempat Tidur (`index.php`)
- Ringkasan total TT, terisi, kosong, dan BOR (Bed Occupancy Rate)
- Tampilan **kartu** atau **tabel** (toggle)
- Filter berdasarkan **kelas** dan **pencarian** nama ruang
- Donut chart per ruangan (CSS `conic-gradient`, tanpa library chart)
- Indikator warna: 🟢 Tersedia (<50%), 🟡 Hampir Penuh (50–79%), 🔴 Penuh/Kritis (≥80%)
- Auto-refresh setiap 30 detik

### 2. Jadwal Dokter HFIS (`index.php`, bagian bawah)
- Menampilkan jadwal dokter **hari ini** otomatis berdasarkan `date('N')`
- Info: nama dokter, poli, sub-spesialis, jam praktik, kuota JKN/Non-JKN
- Status praktik real-time: 🟢 Sedang Praktik / ⚫ Selesai / 🔵 Belum Mulai
- Pencarian dokter/poli
- Dokter yang libur ditampilkan dengan efek grayscale

### 3. Dashboard SATUSEHAT (`satusehat.php`)
- Monitoring resource yang sudah memiliki ID SATUSEHAT
- Ringkasan: total data, terkirim, belum terkirim, persentase
- Filter **rentang tanggal** + shortcut (Hari Ini, 7 Hari, Bulan Ini)
- Indikator warna: 🟢 Baik (≥95%), 🟡 Perlu Perhatian (80–94%), 🔴 Kritis (<80%)
- Auto-refresh setiap 5 menit

### Umum
- Jam digital real-time di header

---

## Struktur File

```
dashboardku/
├── config.php                  # Konfigurasi koneksi database (mysqli)
├── api.php                     # API JSON — data tempat tidur
├── api_jadwal.php              # API JSON — jadwal dokter HFIS hari ini
├── api_satusehat.php           # API JSON — data pengiriman SATUSEHAT
├── index.php                   # Halaman utama (TT + Jadwal Dokter)
├── satusehat.php               # Halaman dashboard SATUSEHAT
├── database.sql                # Referensi struktur tabel tempat_tidur_kemkes
├── README.md                   # Dokumentasi (file ini)
│
├── assets/
│   ├── style.css               # CSS kustom (shared kedua halaman)
│   ├── app.js                  # JS dashboard tempat tidur
│   ├── jadwal.js               # JS jadwal dokter
│   ├── satusehat.js            # JS dashboard SATUSEHAT
│   └── vendor/                 # Library pihak ketiga (lokal, offline-ready)
│       ├── bootstrap/
│       │   ├── bootstrap.min.css          # Bootstrap 5.3.3
│       │   └── bootstrap.bundle.min.js    # Bootstrap 5.3.3 + Popper
│       ├── bootstrap-icons/
│       │   ├── bootstrap-icons.min.css    # Bootstrap Icons 1.11.3
│       │   └── fonts/
│       │       ├── bootstrap-icons.woff2
│       │       └── bootstrap-icons.woff
│       ├── jquery/
│       │   └── jquery-3.7.1.min.js        # jQuery 3.7.1
│       └── poppins/
│           ├── poppins.css                # @font-face declarations
│           ├── poppins-400.woff2          # Regular
│           ├── poppins-500.woff2          # Medium
│           ├── poppins-600.woff2          # SemiBold
│           └── poppins-700.woff2          # Bold
│
└── bahan/                      # Referensi & screenshot (tidak dipakai runtime)
```

---

## Prasyarat

| Kebutuhan | Versi Minimum | Keterangan |
|---|---|---|
| PHP | 7.4+ | Dengan ekstensi `mysqli` aktif |
| MySQL / MariaDB | 5.7+ / 10.3+ | Server database operasional RS |
| Web Server | Apache / Nginx | Laragon sudah termasuk Apache |
| Browser | Chrome, Firefox, Edge | Versi modern |

> **Tidak perlu Composer, Node.js, atau build tools.** Semua library sudah di `assets/vendor/`.

- Navigasi antar dashboard via tombol di header
- Responsive (mobile-friendly)
- **Semua asset lokal** — tidak bergantung CDN, bisa jalan tanpa internet
- XSS-safe — semua output teks di-escape di sisi JavaScript

---

## Instalasi

### Menggunakan Laragon (Rekomendasi)

1. **Clone atau copy** folder project ke document root:
   ```
   C:\laragon\www\dashboardku\
   ```

2. **Start Laragon** — pastikan Apache dan MySQL aktif.

3. **Sesuaikan konfigurasi** di `config.php` (lihat bagian berikutnya).

4. **Akses via browser:**
   ```
   http://localhost/dashboardku/              → Dashboard TT + Jadwal
   http://localhost/dashboardku/satusehat.php → Dashboard SATUSEHAT
   ```

### Web Server Lain

1. Copy seluruh folder ke document root.
2. Pastikan PHP 7.4+ dengan ekstensi `mysqli` aktif.
3. Sesuaikan `config.php`.

---

## Konfigurasi Database

Edit file **`config.php`**:

```php
const DB_HOST = '192.168.100.170';   // IP server database
const DB_NAME = 'informasi';         // Database default
const DB_USER = 'admin';             // Username MySQL
const DB_PASS = '********';          // Password MySQL
```

### Database & Tabel yang Diakses

Aplikasi mengakses **3 database** melalui 1 koneksi:

| Database | Tabel / Objek | Dipakai Oleh | Hak Akses |
|---|---|---|---|
| `informasi` | `tempat_tidur_kemkes` | `api.php` | `SELECT` |
| `regonline` | `jadwal_dokter_hfis` | `api_jadwal.php` | `SELECT` |
| `kemkes-ihs` | SP `dashboardPengiriman_modif` | `api_satusehat.php` | `EXECUTE` |

### Verifikasi Hak Akses

```sql
-- Cek akses SELECT ke tabel TT
SELECT 1 FROM informasi.tempat_tidur_kemkes LIMIT 1;

-- Cek akses SELECT ke jadwal (cross-database)
SELECT 1 FROM regonline.jadwal_dokter_hfis LIMIT 1;

-- Cek akses EXECUTE stored procedure
CALL `kemkes-ihs`.dashboardPengiriman_modif(NOW(), NOW());
```

Jika error, berikan hak akses (jalankan sebagai root/DBA):

```sql
GRANT SELECT ON regonline.jadwal_dokter_hfis TO 'admin'@'%';
GRANT EXECUTE ON PROCEDURE `kemkes-ihs`.dashboardPengiriman_modif TO 'admin'@'%';
FLUSH PRIVILEGES;
```


---

## Endpoint API

Semua API bersifat **read-only** (GET), mengembalikan JSON, tanpa autentikasi.

### `GET api.php` — Data Tempat Tidur

```json
{
  "data": [
    { "nama_ruang": "Melati", "kelas": "3", "terisi": 12, "kosong": 8 }
  ],
  "updated": "2026-10-01 10:30:00"
}
```

> **⚠️ Known Bug:** Kolom `terisi` dan `kosong` keduanya membaca `JMLLAKI` (copy-paste error baris 38-39). Lihat [Troubleshooting](#troubleshooting).

### `GET api_jadwal.php` — Jadwal Dokter Hari Ini

Otomatis filter hari saat request. Hanya dokter `STATUS = 1`.

```json
{
  "hari": "Rabu",
  "tanggal": "2026-10-01",
  "data": [
    {
      "kd_dokter": "D001", "nm_dokter": "dr. Ahmad",
      "kd_poli": "INT", "sub_spesialis": "INT",
      "jam": "Pagi", "jam_mulai": "08:00", "jam_selesai": "12:00",
      "kapasitas": 30, "kuota_jkn": 20, "kuota_non_jkn": 10,
      "libur": false
    }
  ]
}
```

### `GET api_satusehat.php` — Dashboard Pengiriman SATUSEHAT

| Parameter | Wajib | Default | Format |
|---|---|---|---|
| `awal` | Tidak | Hari ini | `YYYY-MM-DD` |
| `akhir` | Tidak | Hari ini | `YYYY-MM-DD` |

Jika `awal > akhir`, otomatis di-swap.

```
api_satusehat.php?awal=2026-10-01&akhir=2026-10-01
```

```json
{
  "awal": "2026-10-01", "akhir": "2026-10-01",
  "updated": "2026-10-01 10:30:00",
  "data": [
    { "nama": "Encounter", "terkirim": 150, "belum": 20, "total": 170 }
  ]
}
```

