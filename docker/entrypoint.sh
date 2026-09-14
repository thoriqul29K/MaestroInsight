#!/bin/sh
set -eu

cd /var/www/html

echo "[entrypoint] Waiting for database ${DB_HOST:-db}:${DB_PORT:-3306}..."

ATTEMPTS=0
MAX_ATTEMPTS=60
until nc -z "${DB_HOST:-db}" "${DB_PORT:-3306}" 2>/dev/null; do
    ATTEMPTS=$((ATTEMPTS + 1))
    if [ "$ATTEMPTS" -ge "$MAX_ATTEMPTS" ]; then
        echo "[entrypoint] Database not reachable after ${MAX_ATTEMPTS} attempts. Aborting." >&2
        exit 1
    fi
    sleep 2
done

echo "[entrypoint] Database is reachable."

if [ ! -f .env ] && [ -f env ]; then
    cp env .env
    echo "[entrypoint] Created .env from env template."
fi

append_env() {
    key="$1"
    val="$2"
    if [ -z "$val" ]; then
        return
    fi
    escaped=$(printf '%s\n' "$val" | sed -e 's/[\/&]/\\&/g')
    if grep -qE "^${key}[[:space:]]*=" .env; then
        sed -i.bak -E "s|^${key}[[:space:]]*=.*|${key} = ${escaped}|" .env
    else
        printf '%s = %s\n' "$key" "$val" >> .env
    fi
}

append_env "app.baseURL"             "${APP_BASE_URL:-http://localhost:8123/}"
append_env "database.default.hostname" "${DB_HOST:-db}"
append_env "database.default.database" "${DB_DATABASE:-db_maestrocrm}"
append_env "database.default.username" "${DB_USERNAME:-root}"
append_env "database.default.password" "${DB_PASSWORD:-${DB_ROOT_PASSWORD:-rootpass}}"
append_env "database.default.port"     "${DB_PORT:-3306}"
append_env "database.default.DBDriver" "${DB_DRIVER:-MySQLi}"
append_env "encryption.key"            "${ENCRYPTION_KEY:-}"
append_env "CI_ENVIRONMENT"            "${CI_ENV_ENV:-production}"

append_env "email.protocol"       "${SMTP_PROTOCOL:-smtp}"
append_env "email.SMTPHost"       "${SMTP_HOST:-}"
append_env "email.SMTPPort"       "${SMTP_PORT:-587}"
append_env "email.SMTPUser"       "${SMTP_USER:-}"
append_env "email.SMTPPass"       "${SMTP_PASS:-}"
append_env "email.SMTPCrypto"     "${SMTP_CRYPTO:-tls}"
append_env "email.fromEmail"      "${EMAIL_FROM:-}"
append_env "email.fromName"       "${EMAIL_FROM_NAME:-Maestro}"
append_env "email.mailType"       "${EMAIL_MAIL_TYPE:-html}"
append_env "email.SMTPTimeout"    "${SMTP_TIMEOUT:-10}"

chown -R www-data:www-data writable
chmod -R ug+rwX writable

if [ "${AUTO_MIGRATE:-0}" = "1" ]; then
    echo "[entrypoint] Running migrations..."
    php spark migrate --all || echo "[entrypoint] Migration failed, continuing."
fi

if [ "${AUTO_OPTIMIZE:-0}" = "1" ]; then
    echo "[entrypoint] Optimizing..."
    php spark optimize || true
fi

exec "$@"