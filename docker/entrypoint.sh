#!/bin/sh
set -e

echo "[entrypoint] Syncing public files to volume..."
cp -rf /var/www/html/public_static/. /var/www/html/public/

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
