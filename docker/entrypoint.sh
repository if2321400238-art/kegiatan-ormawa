#!/bin/sh

set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then

    echo "Installing Composer dependencies..."

    composer install

fi


if [ ! -f .env ]; then

    cp .env.example .env

fi


php artisan key:generate --force

php artisan optimize:clear


if [ "${RUN_MIGRATIONS}" = "true" ]; then

    php artisan migrate --force

fi


exec "$@"