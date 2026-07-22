#!/bin/sh

set -e

cd /var/www/html

echo "Preparing storage..."
mkdir -p \
    storage/framework/cache/data \
    storage/framework/views \
    storage/framework/sessions \
    storage/logs

chown -R www-data:www-data storage bootstrap/cache

# HANYA JALAN DI PRODUCTION
# Di environment local, kita lewati karena folder public akan dilink via Bind Mount Docker
if [ "$APP_ENV" != "local" ]; then
    echo "Refreshing Laravel config cache..."
    php artisan config:clear
    php artisan config:cache

    if [ -d /opt/kegiatan-public-build ]; then
        echo "Syncing public build assets..."
        rm -rf public/build
        cp -R /opt/kegiatan-public-build public/build
        chown -R www-data:www-data public/build
    fi

    if [ -d /opt/kegiatan-public-errors ]; then
        echo "Syncing public error pages..."
        rm -rf public/errors
        cp -R /opt/kegiatan-public-errors public/errors
        chown -R www-data:www-data public/errors
    fi
else
    echo "Local environment detected. Skipping asset copy from /opt."
    # Membersihkan cache bawaan Laravel sangat disarankan untuk dev
    echo "Clearing Laravel caches..."
    php artisan optimize:clear
fi

echo "Creating storage symlink..."
php artisan storage:link --force

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

echo "Clearing compiled Blade views..."
php artisan view:clear

exec "$@"
