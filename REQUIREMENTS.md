# MaestroInsight — Requirements

## 1. Web Server

- PHP built-in server (via `php spark serve`) **or**
- Apache / Nginx with PHP integration

## 2. PHP

| Requirement | Version |
|-------------|---------|
| PHP | **^8.2** |
| Extensions | `mbstring`, `intl`, `mysqli`, `json`, `openssl` (CI4 defaults) |
| Composer | Required for dependency management |

## 3. PHP Dependencies (Composer)

Managed via `composer.json`:

**Production:**
- `codeigniter4/framework: ^4.7`

**Dev (optional):**
- `fakerphp/faker: ^1.9`
- `mikey179/vfsstream: ^1.6`
- `phpunit/phpunit: ^10.5.16`

Install with:
```
composer install
```

## 4. Database

| Property | Value |
|----------|-------|
| Engine | **MySQL** or **MariaDB** |
| Driver | MySQLi |
| Database name | `db_maestrocrm` |
| Charset | `utf8mb4` |

Connection is configured via `.env` keys `database.default.*` or through `app/Config/Database.php`.

## 5. Python (RFM Clustering)

| Requirement | Version / Notes |
|-------------|-----------------|
| Python | **3.x** (3.8+ recommended) |
| On PATH | Interpreter detected as `python`, `python3`, or `py` (in that order) |

### Python Libraries

Install with `pip`:

```bash
pip install numpy pandas scipy scikit-learn pymysql
```

| Library | Purpose |
|---------|---------|
| `numpy` | Numerical operations |
| `pandas` | CSV / DataFrame handling |
| `scipy` | Hierarchical clustering (`scipy.cluster.hierarchy.linkage`) |
| `scikit-learn` | Min-Max normalization (`sklearn.preprocessing.MinMaxScaler`) |
| `pymysql` | Optional — only needed for direct-DB mode (`--db` flag) |

## 6. Environment Configuration

Copy the template and configure:

```bash
cp env .env
```

**Required `.env` keys:**

| Key | Example | Notes |
|-----|---------|-------|
| `CI_ENVIRONMENT` | `development` | Set to `production` in production |
| `app.baseURL` | `http://localhost:8080/` | Must match the dev/prod URL with trailing slash |
| `database.default.*` | hostname, database, username, password, port | MySQL connection |
| `encryption.key` | A 32-char hex string | Session/cookie encryption |
| `CLUSTERING_TIMEOUT` | `300` | Optional, max seconds for Python clustering |

**Email (Promosi feature):**

| Key | Example |
|-----|---------|
| `email.SMTPHost` | `smtp.gmail.com` |
| `email.SMTPUser` | Gmail address |
| `email.SMTPPass` | Gmail App Password (16 chars, requires 2FA enabled) |
| `email.SMTPPort` | `587` |
| `email.SMTPCrypto` | `tls` |

## 7. Directory Permissions (writable)

The following directories must be writable by the web server:

- `writable/uploads/` — RFM CSV input/output, clustering progress JSON
- `writable/logs/` — Clustering debug logs
- `writable/session/` — PHP session files
- `writable/cache/` — CI4 cache

## 8. Testing

- PHPUnit 10.5+ with MySQLi driver
- Tests use the **same MySQL database** — never run against production
- Required config in `phpunit.xml` (gitignored, local only)

Run tests:
```powershell
vendor\bin\phpunit --testdox
```

## 9. Summary Checklist

- [ ] PHP 8.2+ with `mysqli`, `mbstring`, `intl`, `json` extensions
- [ ] Composer installed
- [ ] MySQL / MariaDB server running
- [ ] Database `db_maestrocrm` created
- [ ] `.env` configured (DB, encryption key, base URL)
- [ ] `composer install` completed
- [ ] `php spark migrate` run
- [ ] `php spark db:seed UserSeeder` (default login: `admin` / `admin123`)
- [ ] Python 3 on PATH
- [ ] `pip install numpy pandas scipy scikit-learn pymysql`
- [ ] `writable/uploads/`, `writable/logs/` writable
