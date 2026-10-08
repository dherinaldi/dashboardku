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
- Navigasi antar dashboard via tombol di header
- Responsive (mobile-friendly, Bootstrap 5 grid)
- **Semua asset lokal** — tidak bergantung CDN, bisa jalan tanpa internet
- XSS-safe — semua output teks di-escape di sisi JavaScript

---

## Struktur File

```
dashboardku/
├── .gitignore                  # Exclude config.php & log dari Git
├── config.php                  # Konfigurasi koneksi database ⛔ TIDAK di-commit
├── config.example.php          # Template config (TANPA password, aman di-commit)
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

3. **Buat file konfigurasi** dari template:
   ```
   copy config.example.php config.php
   ```
   Lalu edit `config.php` — isi IP, username, dan password database yang benar.

   > ⛔ **`config.php` tidak di-commit ke Git** (sudah ada di `.gitignore`). Kredensial aman.

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

**Perhitungan:**

| Field JSON | Rumus | Kolom sumber |
|---|---|---|
| `terisi` | `JMLLAKI + JMLPEREMPUAN` | jumlah pasien L + P |
| `kosong` | `max(0, total − terisi)` | sisa tempat tidur |
| `total` *(dihitung di JS)* | `TTLAKI + TTPEREMPUAN` | kapasitas L + P |

> `TTLAKI`/`TTPEREMPUAN` = kapasitas tempat tidur; `JMLLAKI`/`JMLPEREMPUAN` = jumlah terisi. `kosong` dibatasi `max(0, …)` agar tidak minus bila data terisi melebihi kapasitas.

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


---

## Skema Database

### Tabel `informasi.tempat_tidur_kemkes`

| Kolom | Keterangan |
|---|---|
| `KAMAR` | Nama ruang rawat inap |
| `KELAS` | Kelas kamar (1, 2, 3, VIP, dll.) |
| `JENISKAMAR` | Jenis kamar |
| `TTLAKI` | Kapasitas tempat tidur laki-laki |
| `TTPEREMPUAN` | Kapasitas tempat tidur perempuan |
| `JMLLAKI` | Jumlah pasien laki-laki (terisi) |
| `JMLPEREMPUAN` | Jumlah pasien perempuan (terisi) |
| `LASTUPDATED` | Waktu pembaruan data terakhir |

> **Terisi** = `JMLLAKI + JMLPEREMPUAN` &middot; **Total** = `TTLAKI + TTPEREMPUAN` &middot; **Kosong** = `Total − Terisi`.

### Tabel `regonline.jadwal_dokter_hfis`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `KD_DOKTER` | VARCHAR | Kode dokter |
| `NM_DOKTER` | VARCHAR | Nama dokter |
| `KD_POLI` | VARCHAR | Kode poli |
| `KD_SUB_SPESIALIS` | VARCHAR | Sub-spesialis |
| `HARI` | INT | 1=Senin … 7=Minggu |
| `STATUS` | INT | 1=Aktif |
| `JAM` | VARCHAR | Label jam (Pagi/Siang) |
| `JAM_MULAI` | TIME | Jam mulai praktik |
| `JAM_SELESAI` | TIME | Jam selesai praktik |
| `KAPASITAS` | INT | Total kapasitas pasien |
| `KOUTA_JKN` | INT | Kuota pasien JKN |
| `KOUTA_NON_JKN` | INT | Kuota pasien Non-JKN |
| `LIBUR` | INT | 1=Libur, 0=Aktif |

### Stored Procedure `kemkes-ihs`.`dashboardPengiriman_modif`

```sql
CALL `kemkes-ihs`.dashboardPengiriman_modif(@awal, @akhir);
-- Result set: NAMA, MEMILIKI_ID, TDK_MEMILIKI_ID, TOTAL
```


---

## Vendor / Library

Semua library tersimpan lokal di `assets/vendor/` — **tidak ada CDN dependency**, bisa jalan offline.

| Library | Versi | Folder | Sumber Asli |
|---|---|---|---|
| Bootstrap CSS + JS | 5.3.3 | `vendor/bootstrap/` | [getbootstrap.com](https://getbootstrap.com) |
| Bootstrap Icons | 1.11.3 | `vendor/bootstrap-icons/` | [icons.getbootstrap.com](https://icons.getbootstrap.com) |
| jQuery | 3.7.1 | `vendor/jquery/` | [jquery.com](https://jquery.com) |
| Poppins Font | v22 | `vendor/poppins/` | [Google Fonts](https://fonts.google.com/specimen/Poppins) |

### Upgrade Vendor

Download versi baru dan timpa file yang sesuai:

```powershell
# Contoh: upgrade Bootstrap
Invoke-WebRequest -Uri 'https://cdn.jsdelivr.net/npm/bootstrap@5.x.x/dist/css/bootstrap.min.css' `
    -OutFile 'assets/vendor/bootstrap/bootstrap.min.css'
Invoke-WebRequest -Uri 'https://cdn.jsdelivr.net/npm/bootstrap@5.x.x/dist/js/bootstrap.bundle.min.js' `
    -OutFile 'assets/vendor/bootstrap/bootstrap.bundle.min.js'
```

---

## Catatan Teknis

### Urutan Load Script

```html
<!-- jQuery HARUS dimuat pertama (dependency app.js / jadwal.js / satusehat.js) -->
<script src="assets/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
```

### Auto-Refresh

| Halaman | Data | Interval |
|---|---|---|
| `index.php` | Tempat tidur | 30 detik |
| `index.php` | Jadwal dokter | 5 menit |
| `index.php` | Status praktik dokter | 1 menit |
| `satusehat.php` | Data pengiriman | 1 menit |

### Keamanan

- Semua API **read-only** (tidak ada operasi write)
- `api.php` tolak method selain GET (HTTP 405)
- `api_satusehat.php` pakai **prepared statement**
- Output di-escape terhadap XSS di client (fungsi `esc()`)
- `config.php` di-`.gitignore` — kredensial tidak ikut ke repository

---

## Troubleshooting

### Error: "Gagal terhubung ke database"

1. Pastikan MySQL di `192.168.100.170` bisa diakses dari web server
2. Cek kredensial di `config.php`
3. Pastikan ekstensi `mysqli` aktif: `php -m | findstr mysqli`

### Error: "Query jadwal dokter gagal"

User belum punya akses `SELECT` ke database `regonline`:

```sql
GRANT SELECT ON regonline.jadwal_dokter_hfis TO 'admin'@'%';
FLUSH PRIVILEGES;
```

### Error: "Stored procedure tidak dapat dipanggil"

User belum punya hak `EXECUTE`:

```sql
GRANT EXECUTE ON PROCEDURE `kemkes-ihs`.dashboardPengiriman_modif TO 'admin'@'%';
FLUSH PRIVILEGES;
```

### Font atau ikon tidak muncul

Pastikan folder `assets/vendor/` lengkap, terutama:
- `bootstrap-icons/fonts/bootstrap-icons.woff2`
- `poppins/*.woff2`

### Halaman kosong / spinner terus berputar

1. Buka **DevTools** (F12) → tab **Console** — cek error JavaScript
2. Tab **Network** — cek response `api.php` / `api_jadwal.php` (status 500?)
3. Cek log error PHP di Laragon atau `php_error.log`

---

## Lisensi

Internal — untuk keperluan operasional rumah sakit.

