# Laravel API runtime — PHP-FPM + Composer + the extensions the app needs.
FROM php:8.4-fpm-bookworm

# System libs required to build the PHP extensions below.
RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl unzip \
      libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
      libonig-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions:
#   pdo_mysql -> MySQL         redis (pecl) -> cache/queue/session
#   gd + zip  -> dompdf        bcmath -> money math
#   pcntl     -> queue:work graceful shutdown (SIGTERM)
#   intl, mbstring, exif -> common Laravel needs
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath zip gd exif pcntl intl \
 && pecl install redis \
 && docker-php-ext-enable redis

# Composer (copied from the official image — no manual installer).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Dev php.ini overrides (upload size, memory, opcache off for live edits).
COPY docker/php/local.ini /usr/local/etc/php/conf.d/zz-local.ini

WORKDIR /var/www/html
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
