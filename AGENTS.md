# AGENTS.md for MaestroInsight

## Quick start

```powershell
# Setup
cp env .env                     # then edit baseURL + DB settings
composer install

# Run tests (PHPUnit 10.5+)
composer test                   # runs `phpunit`
vendor\bin\phpunit              # Windows direct
vendor\bin\phpunit tests/unit   # single directory

# CLI
php spark                       # list all available CLI commands
php spark make:controller Foo   # generate controller
php spark migrate               # run database migrations
php spark db:seed TestSeeder    # run a seeder

# Dev server
php spark serve                 # starts on localhost:8080
```

## Architecture

- **Framework**: CodeIgniter 4.7+ (PHP 8.2+)
- **Web entrypoint**: `public/index.php` — point web server to `public/`
- **CLI entrypoint**: `spark` (sets FCPATH to `public/`)
- **Route definition**: `app/Config/Routes.php` — currently only `$routes->get('/', 'Home::index')`
- **PSR-4 namespaces**: `App\` → `app/`, `Config\` → `app/Config/`
- **Views**: `app/Views/` — no layout system in use yet (layouts/ and pages/ dirs exist but empty)
- **Migrations/Seeds**: `app/Database/Migrations/`, `app/Database/Seeds/` — none yet (only `.gitkeep`)
- **Static assets**: `public/assets/{css,img,script}/`
- **Writable dirs**: `writable/{cache,logs,session,uploads,debugbar}/` — gitignored contents

## Testing

- Test base class: `CodeIgniter\Test\CIUnitTestCase`
- Test support namespace: `Tests\Support\` → `tests/_support/`
- Test DB defaults to SQLite3 `:memory:` (see `Config\Database::$tests`); set `database.tests.*` in `.env` for real DB
- Existing tests: `tests/unit/HealthTest.php`, `tests/database/ExampleDatabaseTest.php`
- PHPUnit config: `phpunit.dist.xml` (copy to `phpunit.xml` for local overrides)
- Coverage: `phpunit --colors --coverage-text=tests/coverage.txt --coverage-html=tests/coverage/ -d memory_limit=1024m`

## Notable quirks

- `.env` is gitignored; `env` (no dot) is the template committed to the repo
- `CI_ENVIRONMENT` is already uncommented in `.env` and set to `development`
- The root `builds` script toggles the CI4 dependency between `release`, `development`, and `next` branches
- `phpunit.xml` is gitignored — copy from `phpunit.dist.xml` for local overrides
- Coverage reports and PHPUnit cache go into `build/` (gitignored)
- No formatters, linters, or static analysis tools configured beyond PHPUnit
- `app/Filters/`, `app/Helpers/`, `app/Libraries/`, `app/Models/` are empty (only `.gitkeep`)
