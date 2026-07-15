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

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

echo "Clearing compiled Blade views..."
php artisan view:clear

exec "$@"
