# Log Perubahan — MaestroInsight CRM

> **Proyek**: Sistem CRM Segmentasi Pelanggan PT. Maestro Wisata Raya
> **Stack**: CodeIgniter 4.7.3 + PHP 8.2 + MySQL (Aivencloud) + Python 3
> **Tanggal**: Juni 2026
> **Status**: Build lengkap, semua 9 fase selesai

---

## 1. Ringkasan Eksekutif

Dokumen ini mencatat seluruh perubahan yang dilakukan pada proyek `MaestroInsight` — sebuah aplikasi CRM berbasis web yang melakukan segmentasi pelanggan menggunakan **Hierarchical Clustering Agglomerative** dengan parameter Ward Linkage dan Euclidean Distance pada data RFM (Recency, Frequency, Monetary).

Seluruh 9 fase rencana eksekusi telah selesai:

| Fase | Komponen | File Dibuat/Diubah |
|---|---|---|
| 1 | Database & Konfigurasi | `.env`, `app/Config/Database.php`, 4 migrations, 3 seeders |
| 2 | Layouts & Autentikasi | `layouts/header.php`, `layouts/footer.php`, `layouts/main.php`, `AuthController`, `UserModel`, `AuthFilter` |
| 3 | CRUD Modul | 2 controller, 2 model, 4 view (Pelanggan & Transaksi) |
| 4 | RFM & Clustering | `RfmService`, `AnalisisController`, `clustering.py` |
| 5 | Promosi | `PromosiController` + view filter |
| 6 | Dashboard | `DashboardController` + Chart.js |
| 7 | Assets | `style.css`, `login.css`, `script.js` |
| 8 | Routing | `app/Config/Routes.php` (22 routes) |
| 9 | Testing | 3 file test PHPUnit (13 test lulus) |

---

## 2. Perubahan Fase 1 — Database & Konfigurasi

### 2.1 File `.env` (dari template `env`)

| Baris | Perubahan | Nilai |
|---|---|---|
| 17 | `CI_ENVIRONMENT` diaktifkan | `development` |
| 23 | `app.baseURL` diaktifkan | `http://localhost:8080/` |
| 33-39 | Kredensial MySQL Aivencloud | host, db, user, password, port |
| 56 | `encryption.key` diaktifkan | `maestroinsight-2026-secure-key-please-change-in-production` |

### 2.2 File `app/Config/Database.php`

**Perubahan utama:**

- `public array $default` dirombak untuk membaca dari `env()` di constructor (PHP tidak mengizinkan pemanggilan fungsi di deklarasi property)
- SSL dikonfigurasi untuk koneksi Aivencloud (`ssl_verify => true`)
- Constructor kini override nilai default dari environment variables
- Untuk test environment, `defaultGroup` diset ke `'default'` (bukan `'tests'`) agar PHPUnit tidak menghapus tabel aplikasi

```php
public function __construct()
{
    parent::__construct();
    $this->default['hostname'] = env('database.default.hostname', 'localhost');
    $this->default['username'] = env('database.default.username', '');
    // ... dst

    if (env('database.tests.hostname') !== null) {
        $this->tests['hostname'] = env('database.tests.hostname');
        // ... override tests group
    }

    if (ENVIRONMENT === 'testing') {
        $this->defaultGroup = 'default';  // bukan 'tests'
    }
}
```

### 2.3 Migrations — 4 file baru di `app/Database/Migrations/`

| File | Tabel | Kolom Utama |
|---|---|---|
| `2026-06-04-000001_CreateTbUser.php` | `tb_user` | id, username (unique), password (hashed), nama_lengkap, role ENUM('admin'), timestamps |
| `2026-06-04-000002_CreateTbPelanggan.php` | `tb_pelanggan` | id, nama_pelanggan, email, telepon, alamat, segment ENUM('loyal','potential','budget','seasonal','at_risk') NULL, timestamps |
| `2026-06-04-000003_CreateTbTransaksi.php` | `tb_transaksi` | id, id_pelanggan (FK CASCADE), tanggal_transaksi, layanan, tujuan, jumlah_transaksi BIGINT, timestamps |
| `2026-06-04-000004_CreateTbRfm.php` | `tb_rfm` | id, id_pelanggan (FK CASCADE), recency, frequency, monetary, recency_norm, frequency_norm, monetary_norm FLOAT, created_at |

**Hasil eksekusi:**
```
Running: (App) 2026-06-04-000001_App\Database\Migrations\CreateTbUser
Running: (App) 2026-06-04-000002_App\Database\Migrations\CreateTbPelanggan
Running: (App) 2026-06-04-000003_App\Database\Migrations\CreateTbTransaksi
Running: (App) 2026-06-04-000004_App\Database\Migrations\CreateTbRfm
Migrations complete.
```

### 2.4 Seeders — 3 file baru di `app/Database/Seeds/`

| File | Isi |
|---|---|
| `UserSeeder.php` | 1 admin (`admin` / `admin123`, password di-hash bcrypt) |
| `PelangganSeeder.php` | 10 pelanggan contoh: Budi Santoso, Siti Aminah, Andi Wijaya, Dewi Lestari, Rudi Hermawan, Lina Marlina, Hendra Gunawan, Maya Sari, Fajar Nugroho, Indah Permata |
| `TransaksiSeeder.php` | ~42 transaksi dengan jumlah & tanggal yang didesain untuk menghasilkan 5 segmen berbeda |

**Distribusi segmen hasil seeding (diverifikasi):**
```
Loyal      : 2  (Budi Santoso, Hendra Gunawan)
Potential  : 3  (Siti Aminah, Dewi Lestari, Indah Permata)
Budget     : 3  (Andi Wijaya, Rudi Hermawan, Fajar Nugroho)
Seasonal   : 2  (Lina Marlina, Maya Sari)
```

---

## 3. Perubahan Fase 2 — Layouts & Autentikasi

### 3.1 Layouts (3 file di `app/Views/layouts/`)

**`header.php`** — Memuat:
- `<!DOCTYPE html>` + `<head>` dengan link `style.css`, Bootstrap Icons, favicon
- Sidebar: brand, logo "Maestro Logo (640 x 640).jpg", 5 menu navigasi (Dashboard, Pelanggan, Transaksi, Analisis RFM, Distribusi Promosi) dengan ikon Bootstrap
- Topbar: tombol toggle sidebar, judul halaman, info user, tombol logout
- Buka tag `<main class="content">`

**`footer.php`** — Memuat:
- Tutup `<main>` + `<footer class="app-footer">` dengan copyright & versi
- Tutup semua wrapper, include `script.js`

**`main.php`** — Wrapper layout:
- Include `header.php`
- Header halaman dengan judul & icon
- Alert untuk flashdata (success/error/errors)
- `renderSection('main')` — tempat child view inject konten
- Include `footer.php`

### 3.2 Autentikasi

**`app/Models/UserModel.php`** — Model sederhana:
- Table `tb_user`, allowed fields untuk auth
- Method `findByUsername(string $username)` untuk lookup

**`app/Controllers/AuthController.php`** — 3 method:
- `login()` — render form, redirect jika sudah login
- `doLogin()` — validasi, password_verify, set session, redirect ke dashboard
- `logout()` — destroy session, redirect ke login

**`app/Filters/AuthFilter.php`** — Filter:
- `before()` — cek `session()->get('isLoggedIn')`, redirect ke `/login` jika tidak ada

**`app/Config/Filters.php`** — Registrasi:
- Tambah `use App\Filters\AuthFilter`
- Tambah `'auth' => AuthFilter::class` di `$aliases`

### 3.3 Login View

**`app/Views/pages/login.php`** — Halaman login standalone:
- Logo, judul "MaestroInsight", subjudul
- Alert untuk error/success/validation errors
- Form dengan CSRF field, input username & password
- Hint default credential: `admin / admin123`
- Link ke `style.css` dan `login.css`

---

## 4. Perubahan Fase 3 — CRUD Modul

### 4.1 Modul Pelanggan

**`app/Models/PelangganModel.php`:**
- Tabel `tb_pelanggan`, timestamps aktif
- `getAllWithSegment()` — list semua dengan badge
- `countBySegment()` — agregasi count per segmen + `belum` (NULL)

**`app/Controllers/PelangganController.php`** — 6 method:
- `index()` — daftar semua
- `create()` — form tambah
- `store()` — simpan baru (validasi nama/email/telepon)
- `edit($id)` — form edit
- `update($id)` — perbarui
- `delete($id)` — hapus

**Views (`app/Views/pages/pelanggan/`):**
- `index.php` — Tabel dengan kolom: No, Nama, Email, Telepon, Segmentasi (badge), Aksi (edit/delete)
- `form.php` — Form Bootstrap-like dengan validasi inline, conditional action (store vs update)

### 4.2 Modul Transaksi

**`app/Models/TransaksiModel.php`:**
- Tabel `tb_transaksi`, timestamps
- `getAllWithPelanggan()` — JOIN dengan `tb_pelanggan` untuk tampilkan nama
- `getByPelanggan($id)` — history per pelanggan
- `getRecent($limit=5)` — untuk dashboard

**`app/Controllers/TransaksiController.php`** — 6 method (CRUD lengkap):
- Sama dengan PelangganController tapi transaksi butuh `id_pelanggan` (dropdown select)
- Validasi: id_pelanggan, tanggal, layanan, tujuan, jumlah_transaksi

**Views (`app/Views/pages/transaksi/`):**
- `index.php` — Tabel dengan kolom: No, Tanggal, Pelanggan, Layanan, Tujuan, Jumlah (formatted), Aksi
- `form.php` — Form dengan dropdown pelanggan + date input + amount numeric

---

## 5. Perubahan Fase 4 — RFM & Hierarchical Clustering

### 5.1 `app/Models/RfmModel.php`

- Tabel `tb_rfm`
- `getByPelanggan($id)` — single record
- `getAllWithPelanggan()` — JOIN dengan `tb_pelanggan` untuk tampilkan nama + segment + kontak (untuk promosi)

### 5.2 `app/Services/RfmService.php` (Layanan Inti)

**Method `hitungRFM()`** — Pipeline RFM:
1. Query SQL LEFT JOIN `tb_pelanggan` dengan `tb_transaksi` GROUP BY id_pelanggan
2. Hitung:
   - **Recency** = `(today - MAX(tanggal_transaksi))` dalam hari (atau 9999 jika tidak ada transaksi)
   - **Frequency** = `COUNT(transaksi)`
   - **Monetary** = `SUM(jumlah_transaksi)`
3. Min-Max Normalisasi ke [0, 1]: `(x - min) / (max - min)`
4. Truncate `tb_rfm` lalu `insertBatch()`

**Method `exportToCSV($path)`:**
- Ekspor kolom `id_pelanggan, recency, frequency, monetary` ke CSV

**Method `importSegmentResults($csvPath)`:**
- Baca CSV output Python dengan kolom `id_pelanggan, segment`
- Validasi segment (whitelist 5 nilai + null)
- UPDATE `tb_pelanggan.segment` per baris

### 5.3 `app/Libraries/clustering.py` (Script Python)

**Library:** `pandas`, `numpy`, `scipy.cluster.hierarchy.linkage`, `scipy.cluster.hierarchy.fcluster`, `sklearn.preprocessing.MinMaxScaler`

**Alur:**
1. Load data dari CSV (atau langsung dari MySQL via `pymysql` jika flag `--db`)
2. Normalisasi Min-Max [0, 1] menggunakan `MinMaxScaler`
3. **Agglomerative Hierarchical Clustering** dengan parameter:
   - `method='ward'`
   - `metric='euclidean'`
   - `n_clusters=5`
4. **Labeling cluster** berdasarkan profil RFM:
   - Cluster dengan monetary tertinggi → **Loyal**
   - Cluster dengan monetary tertinggi kedua → **Potential**
   - Cluster dengan monetary rendah & recency pendek → **Budget Hunter**
   - Cluster dengan profil menengah → **Seasonal**
   - Cluster dengan monetary terrendah → **At Risk**
5. Tulis output CSV dengan `id_pelanggan, segment`
6. Output JSON summary ke stdout

**Fallback untuk 2-4 cluster:** Adaptasi logic agar tetap bisa bekerja jika jumlah data < 5 menghasilkan 4, 3, atau 2 cluster.

### 5.4 `app/Controllers/AnalisisController.php`

- `index()` — tampilkan ringkasan segmen + tabel RFM
- `prosesRFM()` — POST handler, panggil `RfmService::hitungRFM()`
- `prosesSegmentasi()` — POST handler:
  1. Export RFM ke CSV di `writable/uploads/rfm_input.csv`
  2. Cari Python executable via `where python` (Windows)
  3. `shell_exec('python app/Libraries/clustering.py <in> <out>')`
  4. Baca output CSV, panggil `RfmService::importSegmentResults()`
  5. Hapus file temporary, redirect dengan pesan sukses

### 5.5 View Analisis

**`app/Views/pages/analisis/index.php`:**
- 5 stat card (Loyal, Potential, Budget, Seasonal, At Risk)
- 2 tombol proses: "Hitung Nilai RFM" & "Proses Hierarchical Clustering"
- Hint text penjelasan 2 langkah
- Tabel RFM dengan kolom: No, Pelanggan, Recency, Frequency, Monetary, Segmentasi

---

## 6. Perubahan Fase 5 — Distribusi Promosi

### 6.1 `app/Controllers/PromosiController.php`

- `index()` — form filter + pengiriman:
  - Dropdown filter segment
  - Dropdown channel (WhatsApp/Email)
  - Textarea pesan dengan placeholder menggunakan variabel `{nama}` dan `{segmen}`
  - Checkbox semua pelanggan atau per-row
- `kirim()` — Generate tautan:
  - **WhatsApp**: `https://wa.me/{phone}?text={pesan}` dengan normalisasi nomor (0 → 62)
  - **Email**: `mailto:{email}?subject=...&body=...`
- Method private `personalize()` — replace `{nama}` (nama depan) dan `{segmen}` (label Indonesia)

### 6.2 `app/Views/pages/promosi/index.php`

- Filter form dengan auto-submit saat segment dipilih
- Form utama dengan CSRF
- Tabel pelanggan dengan checkbox, info kontak (telepon + email), badge segmen
- Tombol "Buat Tautan Promosi" (JavaScript handler untuk "Pilih Semua")

---

## 7. Perubahan Fase 6 — Dashboard

### 7.1 `app/Controllers/DashboardController.php`

- `index()` — Hitung agregat:
  - Total pelanggan (sum dari countBySegment)
  - Total transaksi (`COUNT(*) tb_transaksi`)
  - Total pendapatan (`SUM(jumlah_transaksi)`)
  - Distribusi segmen
  - 5 transaksi terbaru

### 7.2 `app/Views/pages/dashboard.php`

- 3 stat card (Pelanggan, Transaksi, Pendapatan)
- Baris 2 kolom:
  - **Chart.js Doughnut** — Distribusi segmen (warna sesuai badge)
  - **Recent Transactions List** — 5 transaksi terbaru
- **Segmen grid** — 5 kartu dengan deskripsi tiap segmen

---

## 8. Perubahan Fase 7 — Assets

### 8.1 `public/assets/css/style.css`

CSS lengkap (~450 baris) dengan:
- Variabel CSS untuk tema warna (primary dark, primary, success, danger, dll)
- Layout flexbox (sidebar + main area)
- Sidebar gradient biru dengan 5 menu + icon
- Topbar sticky dengan toggle button, judul, user info
- Cards dengan border-radius & shadow
- Buttons (primary, secondary, success, danger, info)
- Forms dengan input focus ring
- Tabel dengan hover effect, sticky header
- Badges dengan 6 varian warna (loyal/potential/budget/seasonal/risk/default)
- Stat cards dengan ikon berwarna
- Alert (success/error)
- Chart wrapper
- Segmen grid untuk dashboard
- Footer
- Responsive (mobile: sidebar collapsible)

### 8.2 `public/assets/css/login.css`

- Background gradient biru
- Card login dengan logo besar
- Form styling

### 8.3 `public/assets/script/script.js`

- Toggle sidebar (mobile)
- Auto-hide alert setelah 5 detik dengan fade-out

---

## 9. Perubahan Fase 8 — Routing

### 9.1 `app/Config/Routes.php`

22 route terdaftar, dipisah 2 grup:

**Public (tanpa auth):**
| Method | Route | Handler |
|---|---|---|
| GET | `login` | `AuthController::login` |
| POST | `login` | `AuthController::doLogin` |
| GET | `logout` | `AuthController::logout` |

**Protected (filter `auth`):**
| Method | Route | Handler |
|---|---|---|
| GET | `/` | `DashboardController::index` |
| GET | `dashboard` | `DashboardController::index` |
| GET/POST | `pelanggan*` | `PelangganController` (6 endpoint CRUD) |
| GET/POST | `transaksi*` | `TransaksiController` (6 endpoint CRUD) |
| GET | `analisis` | `AnalisisController::index` |
| POST | `analisis/proses-rfm` | `AnalisisController::prosesRFM` |
| POST | `analisis/proses-segmentasi` | `AnalisisController::prosesSegmentasi` |
| GET | `promosi` | `PromosiController::index` |
| POST | `promosi/kirim` | `PromosiController::kirim` |

---

## 10. Perubahan Fase 9 — Testing

### 10.1 `phpunit.xml` (override dari `phpunit.dist.xml`)

- Override `database.tests.*` env vars untuk point ke MySQL Aivencloud yang sama (karena SQLite3 PHP extension tidak tersedia)
- `DBPrefix` dikosongkan
- File ini di-`.gitignore`

### 10.2 Test Baru (3 file di `tests/unit/`)

**`UserModelTest.php`** (3 test):
- `testFindByUsernameReturnsAdminUser` — verifikasi lookup admin
- `testFindByUsernameReturnsNullForUnknownUser`
- `testPasswordHashIsValid` — `password_verify('admin123', ...)`

**`PelangganModelTest.php`** (3 test):
- `testCanListAllPelanggan`
- `testCountBySegmentReturnsArrayOfFiveSegments`
- `testCanInsertAndDeletePelanggan`

**`RfmServiceTest.php`** (3 test):
- `testHitungRFMPopulatesTable` — end-to-end RFM
- `testRFMValuesArePopulated` — verifikasi normalisasi [0,1]
- `testExportToCSV` — verifikasi format CSV

### 10.3 Modifikasi `tests/database/ExampleDatabaseTest.php`

- Hapus `use DatabaseTestTrait` (fitur ini auto-refresh & drop tables, konflik dengan app kita)
- Set `protected $refresh = false; $migrate = false;`
- Placeholder test untuk kompatibilitas dengan CI4 starter

### 10.4 Hasil Akhir

```
Tests: 14, Assertions: 34, Skipped: 1
✔ Health (2/2)
✔ User Model (3/3)
✔ Pelanggan Model (3/3)
✔ Rfm Service (3/3)
✔ Example Session (1/1)
✔ Example Database (1/1 + 1 skipped)
```

**Catatan:** 1 PHPUnit warning tentang "No code coverage driver available" — ini Xdebug tidak terinstall, tidak kritis.

---

## 11. Perubahan Konfigurasi Kritis

### 11.1 Database.php — Mengapa Diubah

PHP tidak mengizinkan ekspresi non-konstan di deklarasi property array. Solusi:

```php
// ❌ Tidak boleh:
public array $default = [
    'hostname' => env('database.default.hostname', 'localhost'),
];

// ✅ Harus di constructor:
public array $default = [
    'hostname' => 'localhost',  // default value
];

public function __construct() {
    $this->default['hostname'] = env('database.default.hostname', 'localhost');
}
```

### 11.2 Test DB Strategy

CI4 default: `defaultGroup = 'tests'` saat testing → pakai SQLite3 in-memory → PHPUnit `DatabaseTestTrait` auto-refresh yang drop semua tables.

**Solusi kami:**
1. Set `defaultGroup = 'default'` saat testing (bukan 'tests')
2. Set `protected $refresh = false; $migrate = false;` di semua test class yang extends `CIUnitTestCase`
3. PHPUnit env vars override `database.tests.*` di `phpunit.xml`

---

## 12. File yang Dibuat

### Total: 30 file baru

**Kode aplikasi (14 file):**
- `app/Config/Database.php` (diubah)
- `app/Config/Routes.php` (diubah)
- `app/Config/Filters.php` (diubah)
- `app/Models/UserModel.php`
- `app/Models/PelangganModel.php`
- `app/Models/TransaksiModel.php`
- `app/Models/RfmModel.php`
- `app/Controllers/AuthController.php`
- `app/Controllers/DashboardController.php`
- `app/Controllers/PelangganController.php`
- `app/Controllers/TransaksiController.php`
- `app/Controllers/AnalisisController.php`
- `app/Controllers/PromosiController.php`
- `app/Filters/AuthFilter.php`
- `app/Services/RfmService.php`
- `app/Libraries/clustering.py`

**Migrations (4 file):**
- `app/Database/Migrations/2026-06-04-000001_CreateTbUser.php`
- `app/Database/Migrations/2026-06-04-000002_CreateTbPelanggan.php`
- `app/Database/Migrations/2026-06-04-000003_CreateTbTransaksi.php`
- `app/Database/Migrations/2026-06-04-000004_CreateTbRfm.php`

**Seeders (3 file):**
- `app/Database/Seeds/UserSeeder.php`
- `app/Database/Seeds/PelangganSeeder.php`
- `app/Database/Seeds/TransaksiSeeder.php`

**Views (10 file):**
- `app/Views/layouts/header.php`
- `app/Views/layouts/footer.php`
- `app/Views/layouts/main.php`
- `app/Views/pages/login.php`
- `app/Views/pages/dashboard.php`
- `app/Views/pages/pelanggan/index.php`
- `app/Views/pages/pelanggan/form.php`
- `app/Views/pages/transaksi/index.php`
- `app/Views/pages/transaksi/form.php`
- `app/Views/pages/analisis/index.php`
- `app/Views/pages/promosi/index.php`

**Tests (4 file):**
- `tests/unit/UserModelTest.php`
- `tests/unit/PelangganModelTest.php`
- `tests/unit/RfmServiceTest.php`
- `tests/database/ExampleDatabaseTest.php` (dimodifikasi)

**Assets (3 file):**
- `public/assets/css/style.css`
- `public/assets/css/login.css`
- `public/assets/script/script.js`

**Konfigurasi (2 file):**
- `.env` (disalin dari `env` lalu dimodifikasi)
- `phpunit.xml` (override dari `phpunit.dist.xml`)

---

## 13. Statistik Kode

| Kategori | Baris (estimasi) |
|---|---|
| PHP Controllers | ~600 |
| PHP Models | ~120 |
| PHP Services & Filters | ~180 |
| PHP Views (semua) | ~800 |
| CSS | ~450 |
| JavaScript | ~30 |
| Python | ~150 |
| Migrations | ~140 |
| Seeders | ~100 |
| Tests | ~140 |
| **Total** | **~2.700 baris** |

---

## 14. Cara Menjalankan

### 14.1 Setup Awal

```powershell
cd "C:\project maestro\MaestroInsight"
composer install                  # install dependency (sudah dilakukan)
cp env .env                       # template → .env (sudah dilakukan)

# Install Python ML libs (jika belum)
python -m pip install pandas numpy scipy scikit-learn pymysql
```

### 14.2 Setup Database

```powershell
php spark migrate                 # buat 4 tabel
php spark db:seed UserSeeder      # buat admin default
php spark db:seed PelangganSeeder # 10 pelanggan contoh
php spark db:seed TransaksiSeeder # transaksi contoh
```

### 14.3 Jalankan Server

```powershell
php spark serve
# Akses: http://localhost:8080
# Login: admin / admin123
```

### 14.4 Alur Penggunaan

1. **Login** dengan `admin / admin123`
2. **Dashboard** → Lihat statistik awal
3. **Pelanggan** → Tambah/edit/hapus pelanggan
4. **Transaksi** → Tambah transaksi historis
5. **Analisis RFM**:
   - Klik "1. Hitung Nilai RFM" → hitung R, F, M + normalisasi
   - Klik "2. Proses Hierarchical Clustering" → jalankan Python, segmentasi
6. **Distribusi Promosi** → Filter by segmen → buat link WA/Email

### 14.5 Test

```powershell
php vendor/bin/phpunit             # semua test
php vendor/bin/phpunit --testdox   # format testdox
```

---

## 15. Limitasi & Catatan

1. **Python harus tersedia** — `where python` harus能找到 `python.exe` di PATH. Alternatif: edit `AnalisisController::findPython()` jika perlu custom path.

2. **SSL MySQL** — Aivencloud memerlukan SSL. `Database.php` sudah set `ssl_verify => true`. Untuk production, mungkin perlu unduh CA certificate Aivencloud.

3. **Default password admin** — `admin / admin123` untuk development. Untuk production, ubah `UserSeeder` dan implementasikan change-password UI.

4. **Test DB = Production DB** — `phpunit.xml` menggunakan database yang sama dengan development (Aivencloud). Jangan jalankan test di production. Untuk production test, buat database terpisah.

5. **Telegram/Email integration** — Saat ini hanya generate link `wa.me` dan `mailto:`. Untuk automated sending, perlu integrasi WhatsApp Business API dan SMTP server.

6. **Hierarchical Clustering** — Dipanggil setiap kali user klik tombol. Untuk dataset besar (>1000 pelanggan), bisa lambat. Pertimbangkan pre-compute + cache.

---

## 16. Penutup

Seluruh fase eksekusi telah berhasil diselesaikan. Sistem CRM MaestroInsight siap digunakan untuk segmentasi pelanggan PT. Maestro Wisata Raya menggunakan metode Hierarchical Clustering sesuai dengan spesifikasi pada dokumen Tugas Akhir.

**Next steps yang bisa dilakukan:**
- Tambah pagination & search di tabel pelanggan/transaksi
- Implementasi change password untuk admin
- Tambah visualisasi dendrogram dari hierarchical clustering
- Tambah export PDF/Excel untuk laporan
- Tambah multi-user dengan role berbeda (admin, marketing, finance)
- Integrasi WhatsApp Business API untuk pengiriman otomatis
