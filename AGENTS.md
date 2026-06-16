# AGENTS.md — MaestroInsight

> CodeIgniter 4.7 / PHP 8.2 CRM for PT. Maestro Wisata Raya. Customer segmentation
> via Python-driven Hierarchical Clustering (Ward, Euclidean) over RFM features.
> Indonesian UI, single admin role.

## First-time setup (Windows / PowerShell)

```powershell
# 1. MySQL/MariaDB must be running. XAMPP is the local stack.
C:\xampp\mysql_start.bat

# 2. Create the database (migrations won't create it).
php -r "$c=new mysqli('localhost','root','',null,3306);if($c->connect_error){exit(1);}$c->query('CREATE DATABASE IF NOT EXISTS db_maestrocrm CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');"

# 3. PHP deps + migrations + seeders.
composer install
cp env .env
php spark migrate
php spark db:seed UserSeeder
php spark db:seed PelangganSeeder
php spark db:seed TransaksiSeeder

# 4. Dev server. Default login: admin / admin123 (UserSeeder).
php spark serve
# → http://localhost:8080/login
```

## Database

- `.env` targets `db_maestrocrm` at `localhost:3306`, user `root`, no password (XAMPP default).
- `app/Config/Database.php`: `$default['encrypt'] = false` (set to `false`, not array — the SSL array breaks on local MySQL). Env values are read in `__construct` because PHP can't call `env()` in property defaults. New `database.default.*` keys must go in the constructor too.
- When `ENVIRONMENT === 'testing'`, `defaultGroup` is forced to `'default'` so tests run against the same MySQL DB (no separate test schema).

## Layout & views

- `app/Views/layouts/main.php` wraps `header.php` + `footer.php`. Page views extend `layouts/main` via `<?= $this->extend('layouts/main') ?>` and fill `<?= $this->section('main') ?>`.
- Pages supply `$pageTitle`, `$pageIcon`, `$title`. Optional `$pageActions` renders action buttons in the header. Flashdata alerts (`success`, `error`, `errors`) are rendered automatically by `main.php` — don't duplicate.
- Flat views: `app/Views/pages/{login,dashboard}.php`. Per-section: `pages/{pelanggan,transaksi,analisis,promosi}/{index,form,riwayat}.php`.

## Routes

- `app/Config/Routes.php`. Public: `GET /` → `AuthController::login`, `POST login` → `doLogin`, `GET logout` → `logout`. Everything else is inside `$routes->group('', ['filter' => 'auth'], ...)` — `AuthFilter` (`app/Filters/AuthFilter.php`) checks `session()->get('isLoggedIn')`. New protected routes must go inside that group.
- Single role admin (migration `DropRoleFromTbUser` removed the `role` column from `tb_user`).
- Available endpoints: `pelanggan` CRUD, `transaksi` CRUD, `analisis` (RFM view + POST `/analisis/proses-rfm-cluster` + GET `/analisis/progress`), `promosi` (POST `/promosi/kirim`, GET `/promosi/riwayat`, POST `/promosi/riwayat/hapus`).

## Python integration (RFM clustering)

- `app/Libraries/clustering.py` is invoked by `AnalisisController::prosesRFMCluster` via `shell_exec`. Do not rename or move either without updating the other.
- Required: `pip install pandas numpy scipy scikit-learn pymysql`. The interpreter (`python`/`python3`/`py`) must be on PATH — probed in that order by `findPython()`.
- Flow: `RfmService::exportToCSV()` writes `writable/uploads/rfm_input.csv` → Python reads it, runs Ward linkage (Euclidean, 5 clusters), writes `writable/uploads/rfm_output.csv` → `importSegmentResults()` updates `tb_pelanggan.segment` and writes normalized RFM values back to `tb_rfm`.
- **Segments (5)**: `Loyal`, `Potential`, `Budget Hunter`, `Seasonal`, `At Risk` — assigned by ranking cluster means on monetary value. DB values are lowercase: `loyal`, `potential`, `budget`, `seasonal`, `at_risk`.
- **Destructive**: `RfmService::hitungRFM()` does `$this->rfm->db->table('tb_rfm')->truncate()` then re-inserts. Don't call it during concurrent reads of `tb_rfm`. Not safe on production DB without backups.
- 5-min timeout (`CLUSTERING_TIMEOUT` in `.env`, default 300s). Progress written to `writable/uploads/clustering_progress.json` (polled by GET `/analisis/progress`). Debug log at `writable/logs/clustering_debug-YYYY-MM-DD.log`.
- `clustering.py` also supports a `--db` mode (reads directly from MySQL via `pymysql`); currently unused — PHP uses CSV round-trip.

## Promosi (email sender)

- `app/Libraries/Promosi/PromosiSender.php` is a registry. Only `EmailChannel` is wired by default — uses SMTP from `.env` (`maestrotour2026@gmail.com` + 16-char app password, Gmail SMTP, configured in `app/Config/Email.php`).
- Implements `ChannelInterface` (returns `SendResult`) to add new channels. Every send is logged to `tb_promosi_log` via `app/Models/PromosiLogModel.php`.

## Testing

- `phpunit.xml` (gitignored) already exists locally, overriding tests to use `localhost/db_maestrocrm` with MySQLi. Without it, tests fall back to the framework's SQLite3 in-memory defaults, which fail because `sqlite3` is not installed. New devs: copy `phpunit.dist.xml` to `phpunit.xml` and uncomment/set the `<env name="database.tests.*">` block.
- Tests use the **same MySQL DB as development**. Do not run against production.
- Every test class extending `CIUnitTestCase` **must** set:
  ```php
  protected $refresh = false;
  protected $migrate = false;
  ```
  Without these, CI4's `DatabaseTestTrait` drops all tables on every run.
- Run commands:
  ```powershell
  vendor\bin\phpunit --testdox
  vendor\bin\phpunit --testdox --filter Rfm
  vendor\bin\phpunit tests\unit
  ```
- Known: no Xdebug (coverage warning is non-critical). `tests/database/ExampleDatabaseTest.php::testSoftDeleteLeavesRow` is intentionally skipped.

## Migrations & seeders

- `app/Database/Migrations/` has the full schema. Recent migrations (2026-06-08) add `agama`, `tanggal_lahir`, `profesi` to `tb_pelanggan`; make `email` / `layanan` / `tujuan` nullable; add `attachment_filename` to `tb_promosi_log`. Always run `php spark migrate` on a fresh checkout.
- Two alternative seeders for bulk CSV import exist but require files at `writable/uploads/DATA PELANGGAN 1.csv` and `writable/uploads/DATA TRANSAKSI PELANGGAN.csv`:
  ```powershell
  php spark db:seed ImportPelangganCsvSeeder
  php spark db:seed ImportTransaksiCsvSeeder
  ```

## Quirks & security

- **SECURITY WARNING**: `.env` contains a **live Aiven production database password** (`AVNS_aTAApyxEp4CwtIflYD9` for `avnadmin@maestroinsight-1-sipsp-e071.l.aivencloud.com:27714`). Any agent or contributor with access to `.env` can connect to the production DB. Rotate this credential immediately.
- `.env` is gitignored; `env` (no dot) is the committed template — they diverge.
- `encryption.key` in `.env` is a dev placeholder (must change for production).
- `app.baseURL` in `.env`: the LAN IP (`http://192.168.100.9:8080/`) line is commented out; `localhost:8080` is active. If deploying to a LAN, swap which line is uncommented or assets will 404.
- `app/Config/WorkerMode.php` is stock CI4 FrankenPHP worker mode config — not a custom file.
- No linters, formatters, static analysis, CI workflows, or pre-commit hooks.
