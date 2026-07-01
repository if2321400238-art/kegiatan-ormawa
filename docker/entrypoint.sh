#!/bin/sh

set -e

cd /var/www/html

echo "Starting entrypoint script..."

# Refresh hanya aset Vite di shared public volume. Direktori public lainnya
# (termasuk file upload) tetap dipertahankan.
if [ -d /opt/kegiatan-public-build ]; then

    echo "Synchronizing frontend build assets..."
    rm -rf public/build
    mkdir -p public/build
    cp -a /opt/kegiatan-public-build/. public/build/

fi

# Always ensure composer dependencies are installed
echo "Installing/updating Composer dependencies..."

composer install --ignore-platform-req=ext-gd --prefer-dist --no-progress --no-interaction

echo "Composer install completed"

# Check if Laravel is properly set up by testing the bootstrap
echo "Testing Laravel bootstrap..."

if php -r "require_once 'vendor/autoload.php'; echo 'OK';" 2>/dev/null | grep -q OK; then

    echo "Laravel loaded successfully"

    if [ ! -f .env ]; then

        echo "Creating .env file..."
        cp .env.example .env

    fi

    php artisan key:generate --force
    php artisan optimize:clear

    if [ "${RUN_MIGRATIONS}" = "true" ]; then

        echo "Running migrations..."
        php artisan migrate --force

    fi

else

    echo "WARNING: Laravel failed to load, skipping artisan commands"

fi

echo "Entrypoint completed, starting PHP-FPM..."

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
