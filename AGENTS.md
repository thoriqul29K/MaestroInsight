# AGENTS.md — MaestroInsight

> CodeIgniter 4.7 / PHP 8.2 CRM for PT. Maestro Wisata Raya. Customer segmentation
> via Python-driven Hierarchical Clustering (Ward, Euclidean) over RFM features.
> Indonesian UI, single admin role.

## First-time setup (Windows / PowerShell)

```powershell
# 1. MySQL/MariaDB must be running. XAMPP is the local stack.
C:\xampp\mysql_start.bat

# 2. Create the database (migrations won't create it).
#    Uses bundled PHP, no mysql CLI required.
php -r "$c=new mysqli('localhost','root','',null,3306);if($c->connect_error){exit(1);}$c->query('CREATE DATABASE IF NOT EXISTS db_maestrocrm CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');"

# 3. PHP deps + migrations + seeders.
composer install
cp env .env                    # .env is already configured for local XAMPP
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
- `app/Config/Database.php` `$default['encrypt']` is **`false`** — the framework's SSL array silently breaks on a local MySQL with no SSL configured. If you switch to a remote MySQL that requires SSL (e.g. Aivencloud), re-add the `encrypt` array with `ssl_verify => true`.
- PHP doesn't allow `env()` calls in property defaults, so the env values are read in the **`__construct`** of `Database.php`. New `database.default.*` keys must be added there too.
- `defaultGroup` is forced to `'default'` when `ENVIRONMENT === 'testing'`. Tests therefore run against the same DB as development (no separate test schema).

## Layout & views

- `app/Views/layouts/header.php` + `footer.php` = sidebar, topbar, scripts. `layouts/main.php` wraps them and exposes a `main` section.
- Page views extend `layouts/main` and put content in `<?= $this->section('main') ?>`:
  - Flat: `app/Views/pages/{login,dashboard}.php`
  - Per-section: `app/Views/pages/{pelanggan,transaksi,analisis,promosi}/{index,form,riwayat}.php`
- Pages set `pageTitle`, `pageIcon`, `title` for the chrome to read. Flashdata alerts are rendered automatically by `layouts/main.php` — don't duplicate.
- Static assets live in `public/assets/{css,img,script}/`. Logo: `public/assets/img/Maestro Logo (640 x 640).jpg`.

## Routes

- `app/Config/Routes.php`. Public: `login` (GET+POST), `logout`. Everything else is inside `$routes->group('', ['filter' => 'auth'], ...)` — the `AuthFilter` in `app/Filters/AuthFilter.php` redirects unauthenticated users to `/login`. New protected routes must go inside that group.
- Single role: `admin`. `tb_user.role` is `ENUM('admin')`.

## Python integration (RFM clustering)

- `app/Libraries/clustering.py` is invoked by `app/Controllers/AnalisisController.php::prosesRFMCluster` via `shell_exec`. Do not rename or move either without updating the other.
- Required Python packages: `pandas`, `numpy`, `scipy`, `sklearn`, `pymysql`. Install once: `pip install pandas numpy scipy scikit-learn pymysql`. `python` (or `python3`/`py`) must be on PATH — the controller probes in that order.
- Flow: controller writes `writable/uploads/rfm_input.csv` → runs Python → reads `writable/uploads/rfm_output.csv` → updates `tb_pelanggan.segment`.
- **Destructive**: `RfmService::hitungRFM()` does `$this->rfm->db->table('tb_rfm')->truncate()` then re-inserts. Don't call it while a read of `tb_rfm` is in flight, and don't run it on a production DB without backups.
- The endpoint has a 3-minute timeout and writes a `writable/uploads/clustering_progress.json` consumed by `GET /analisis/progress` for an AJAX progress bar. Clustering log is at `writable/clustering_log.txt`.

## Promosi (notification sender)

- `app/Libraries/Promosi/PromosiSender.php` is a registry. Only `EmailChannel` is wired in by default — it uses the SMTP settings in `.env` (Gmail + 16-char app password).
- To add a channel, implement `ChannelInterface` (returns `SendResult`) and register it in `PromosiSender::__construct()`.
- Every send attempt is logged to `tb_promosi_log` via `app/Models/PromosiLogModel.php`. View at `GET /promosi/riwayat`.

## Email (SMTP)

- Already configured in `.env`: `maestrotour2026@gmail.com` + app password. Consumed by `app/Config/Email.php`. The from-name is `"Maestro Wisata Raya"`.

## Testing

- `phpunit.dist.xml` is committed. **`phpunit.xml` is gitignored** — without a local copy, tests fall back to the framework's hard-coded SQLite3 in-memory defaults, which fail because the PHP `sqlite3` extension is not installed on this machine. Create `phpunit.xml` from `phpunit.dist.xml` (the committed one overrides the test DB to `localhost/db_maestrocrm`).
- Tests use the **same MySQL DB as development** (see Database above). Do not run `phpunit` against a production DB.
- Every test class extending `CIUnitTestCase` MUST set:
  ```php
  protected $refresh = false;
  protected $migrate = false;
  ```
  Otherwise CI4's `DatabaseTestTrait` drops all 4 app tables on every run.
- Run commands:
  ```powershell
  vendor\bin\phpunit --testdox
  vendor\bin\phpunit --testdox --filter "Rfm"
  vendor\bin\phpunit tests\unit
  ```
- 1 known non-critical warning: "No code coverage driver available" (Xdebug not installed). One test in `tests/database/ExampleDatabaseTest.php` is `markTestSkipped` — that's intentional.

## Quirks worth knowing

- `.env` is gitignored; `env` (no dot) is the committed template. The two diverge — `.env` is the live config.
- `encryption.key` in `.env` is a dev placeholder. Change before any production use.
- Gitignored: `vendor/`, `build/`, `phpunit.xml`, `tests/coverage*`, `php_errors.log`, OS junk files. See `.gitignore`.
- Composer starter (`codeigniter4/appstarter`) — `composer.json` is mostly the upstream template.
- No linters, formatters, or static analysis configured. No CI workflows. No pre-commit hooks.
