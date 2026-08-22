#!/bin/sh
set -e
cd /var/www/html

# Bootstrap .env from example if it doesn't exist
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "[entrypoint] Created .env from .env.example"
    else
        echo "[entrypoint] WARNING: No .env or .env.example found"
    fi
fi

# Sync critical env vars from the container environment into .env.
# docker-compose passes these at runtime; writing them to .env ensures artisan
# commands and any tooling that reads .env directly see the correct values.
_sync_env() {
    local key="$1"
    local val
    # printenv exits 1 when the key is unset; with set -e that would abort the whole entrypoint.
    val=$(printenv "$key" 2>/dev/null || true)
    [ -z "$val" ] && return 0
    if grep -q "^${key}=" .env 2>/dev/null; then
        sed -i "s|^${key}=.*|${key}=${val}|" .env
    else
        echo "${key}=${val}" >> .env
    fi
}

for _var in \
    APP_KEY APP_URL APP_ENV \
    QUEUE_CONNECTION CACHE_STORE SESSION_DRIVER \
    MAIL_MAILER MAIL_SCHEME MAIL_HOST MAIL_PORT \
    MAIL_USERNAME MAIL_PASSWORD MAIL_FROM_ADDRESS MAIL_FROM_NAME \
    MAIL_ENCRYPTION MAIL_VERIFY_PEER \
    FAWATERK_API_KEY FAWATERK_VENDOR_KEY FAWATERK_SANDBOX FAWATERK_API_BASE_URL FAWATERK_WEBHOOK_SECRET \
    GEMINI_API_KEY OPENROUTER_API_KEY OPENAI_API_KEY \
    VIDANEXUS_PRELAUNCH VIDANEXUS_BYPASS_TOKEN; do
    _sync_env "$_var"
done
unset _var

# Generate APP_KEY if missing or empty
if [ -z "$(grep -E '^APP_KEY=.+' .env 2>/dev/null)" ]; then
    php artisan key:generate --force --ansi
    echo "[entrypoint] APP_KEY generated"
fi

# Prod image: .env is created/updated above as root; php-fpm runs as www-data. Without this,
# Horizon "Global Infrastructure (.env)" and similar file_put_contents(.env) calls fail.
if [ -f .env ]; then
    chown www-data:www-data .env 2>/dev/null || true
    chmod 664 .env 2>/dev/null || true
fi

# Seed database.sqlite from the host copy if the container's file is empty/missing.
# /tmp/seed.sqlite is bind-mounted read-only from the host (see docker-compose.yml).
if [ -f /tmp/seed.sqlite ] && [ ! -s database/database.sqlite ]; then
    mkdir -p database
    cp /tmp/seed.sqlite database/database.sqlite
    echo "[entrypoint] Seeded database.sqlite from host"
fi

# Ensure all writable subdirs exist.
# storage_data is a named volume (starts empty), database may be a bind mount.
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

# Fix ownership only when the directory is not already owned by www-data.
# This avoids a slow recursive chown on large existing volumes.
WWWDATA_UID=$(id -u www-data 2>/dev/null || echo 82)
for dir in storage bootstrap/cache database; do
    [ -d "$dir" ] || continue
    if [ "$(stat -c '%u' "$dir" 2>/dev/null)" != "$WWWDATA_UID" ]; then
        # chown often fails on Docker Desktop Windows bind mounts; do not abort the container.
        if ! chown -R www-data:www-data "$dir" 2>/dev/null; then
            echo "[entrypoint] warning: chown www-data skipped for $dir (bind mount / remote FS)" >&2
        fi
        chmod -R ug+rwX "$dir" 2>/dev/null || true
    fi
done

# Always ensure the SQLite file exists and is writable (bind mount may be empty on first run).
touch database/database.sqlite 2>/dev/null || true
chown www-data:www-data database/database.sqlite 2>/dev/null || true
chmod 664 database/database.sqlite 2>/dev/null || true

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

# Dev containers can start from an image-copied vendor tree that was built
# with --no-dev; ensure local-only packages (like laravel/pail) exist.
if [ "${APP_ENV:-local}" != "production" ] && [ ! -d vendor/laravel/pail ]; then
    composer install --no-interaction --prefer-dist
fi

if [ "${APP_ENV:-local}" != "production" ]; then
    rm -f bootstrap/cache/packages.php bootstrap/cache/services.php 2>/dev/null || true
    php artisan config:clear --no-ansi 2>/dev/null || true
    php artisan route:clear --no-ansi 2>/dev/null || true
    php artisan view:clear --no-ansi 2>/dev/null || true
    php artisan event:clear --no-ansi 2>/dev/null || true
fi

if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
    php artisan migrate --force --no-interaction
fi

exec docker-php-entrypoint "$@"
