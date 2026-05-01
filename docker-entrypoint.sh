#!/bin/sh

set -e
set -x

echo "=== ENTRYPOINT STARTED ==="

max_attempts="${DB_WAIT_ATTEMPTS:-30}"
sleep_seconds="${DB_WAIT_SECONDS:-2}"
attempt=1

echo "[startup] Verifying database connection before migrations..."

while [ "$attempt" -le "$max_attempts" ]; do
    echo "[startup] Database check attempt ${attempt}/${max_attempts}"

    if php artisan db:show --no-interaction; then
        echo "[startup] Database connection verified."
        break
    fi

    if [ "$attempt" -eq "$max_attempts" ]; then
        echo "[startup] Database connection verification failed after ${max_attempts} attempts." >&2
        php artisan db:show --no-interaction
        exit 1
    fi

    echo "[startup] Database not ready yet. Retrying in ${sleep_seconds}s..."
    sleep "$sleep_seconds"
    attempt=$((attempt + 1))
done

echo "[startup] Running database migrations..."
php artisan migrate --force
echo "[startup] Database migrations completed."

echo "[startup] Clearing Laravel optimization caches..."
php artisan optimize:clear
echo "[startup] Laravel optimization caches cleared."

echo "[startup] Starting PHP development server on 0.0.0.0:${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public
