#!/bin/sh
set -e

echo "[entrypoint] Syncing public files to volume..."
cp -rf /var/www/html/public_static/. /var/www/html/public/

echo "[entrypoint] Waiting for database (${DB_HOST}:${DB_PORT})..."
until php -r '
    try {
        new PDO(
            "mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
    } catch (Throwable $e) {
        fwrite(STDERR, $e->getMessage().PHP_EOL);
        exit(1);
    }
'; do
    echo "[entrypoint] DB not ready yet, retry in 2s..."
    sleep 2
done
echo "[entrypoint] Database is up."

echo "[entrypoint] Running migrations..."
php artisan migrate --force

echo "[entrypoint] Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "[entrypoint] Caching config / routes / views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] Starting PHP-FPM..."
exec php-fpm
