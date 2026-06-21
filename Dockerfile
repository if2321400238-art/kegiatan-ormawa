FROM php:8.3-fpm-bookworm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    unzip \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libonig-dev \
    libzip-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    mbstring \
    pcntl \
    pdo_mysql \
    zip \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node
COPY --from=node:22-bookworm-slim /usr/local /usr/local

COPY docker/entrypoint.sh /usr/local/bin/kegiatan-entrypoint

RUN chmod +x /usr/local/bin/kegiatan-entrypoint

ENTRYPOINT ["kegiatan-entrypoint"]

CMD ["php-fpm"]