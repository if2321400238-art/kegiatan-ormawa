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

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

exec "$@"
