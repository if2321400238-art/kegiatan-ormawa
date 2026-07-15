# ==================================================
# Base PHP
# ==================================================
FROM php:8.3-fpm-bookworm AS php-base

WORKDIR /var/www/html

# Menggunakan php-fpm (bukan cli) karena di production dihubungkan ke Nginx
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    zip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    mbstring \
    pcntl \
    pdo_mysql \
    zip \
 && { \
    echo 'pm.max_children = 12'; \
    echo 'pm.start_servers = 3'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 5'; \
    echo 'pm.max_requests = 500'; \
  } >> /usr/local/etc/php-fpm.d/zz-kegiatan-pm.conf \
 && { \
    echo 'upload_max_filesize = 16M'; \
    echo 'post_max_size = 32M'; \
    echo 'max_file_uploads = 20'; \
    echo 'memory_limit = 256M'; \
  } > /usr/local/etc/php/conf.d/kegiatan-uploads.ini \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# ==================================================
# Composer dependency
# ==================================================
FROM php-base AS composer-deps

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts


# ==================================================
# Frontend dependency (Proses Kompilasi Tailwind/Vite)
# ==================================================
FROM node:22-bookworm-slim AS frontend-builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./

RUN npm run build


# ==================================================
# Application Final Stage
# ==================================================
FROM php-base AS app

WORKDIR /var/www/html

# 1. Copy full source code terlebih dahulu
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY artisan ./
COPY composer.json ./
COPY composer.lock ./
COPY .env.example ./

# 2. Copy vendor (depedensi PHP)
COPY --from=composer-deps /app/vendor ./vendor

# 3. Copy aset hasil compile Tailwind/Vite (PENTING!)
COPY --from=frontend-builder /app/public/build ./public/build

# Simpan salinan build di luar /public. Pada runtime, /public dipasang sebagai
# named volume sehingga isi build dari image dapat tertutup oleh volume lama.
COPY --from=frontend-builder /app/public/build /opt/kegiatan-public-build

COPY docker/entrypoint.sh /usr/local/bin/kegiatan-entrypoint

RUN chmod +x /usr/local/bin/kegiatan-entrypoint \
 && mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

# Production menggunakan port 9000 untuk PHP-FPM berkomunikasi dengan Nginx
EXPOSE 9000

ENTRYPOINT ["kegiatan-entrypoint"]

CMD ["php-fpm"]
