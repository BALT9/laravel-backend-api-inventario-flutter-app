#!/bin/sh

set -e

CONTAINER_ROLE="${CONTAINER_ROLE:-app}"
DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_WAIT_RETRIES="${DB_WAIT_RETRIES:-30}"

mkdir -p \
    bootstrap/cache \
    storage/api-docs \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data bootstrap/cache storage
chmod -R ug+rwX bootstrap/cache storage

if [ -z "${APP_KEY:-}" ]; then
    echo "WARNING: APP_KEY is empty. Set it in .env before serving requests." >&2
    if [ "${APP_ENV:-production}" = "production" ]; then
        exit 1
    fi
fi

if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Waiting for PostgreSQL at ${DB_HOST:-127.0.0.1}:${DB_PORT:-5432}..."

    attempt=1

    until php -r '
        try {
            $host = getenv("DB_HOST") ?: "127.0.0.1";
            $port = getenv("DB_PORT") ?: "5432";
            $database = getenv("DB_DATABASE") ?: "";
            $username = getenv("DB_USERNAME") ?: "";
            $password = getenv("DB_PASSWORD") ?: "";

            if ($database === "") {
                exit(0);
            }

            new PDO(
                "pgsql:host={$host};port={$port};dbname={$database};connect_timeout=3",
                $username,
                $password
            );
        } catch (Throwable $exception) {
            exit(1);
        }
    '; do
        if [ "$attempt" -ge "$DB_WAIT_RETRIES" ]; then
            echo "PostgreSQL is not reachable after ${DB_WAIT_RETRIES} attempts." >&2
            exit 1
        fi

        attempt=$((attempt + 1))
        sleep 2
    done
fi

if [ "$CONTAINER_ROLE" = "queue" ]; then
    exec "$@"
fi

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    php artisan storage:link
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${APP_OPTIMIZE:-}" = "true" ] || [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan view:cache
else
    php artisan config:clear
    php artisan view:clear
fi

if [ "${GENERATE_SWAGGER:-true}" = "true" ]; then
    php artisan l5-swagger:generate
fi

exec "$@"
