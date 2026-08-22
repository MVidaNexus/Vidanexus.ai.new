# syntax=docker/dockerfile:1

FROM node:22-alpine AS frontend
WORKDIR /frontend
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
COPY Modules ./Modules
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs

FROM php:8.2-fpm-alpine AS php

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    linux-headers \
    libpq-dev \
    supervisor \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY deploy/supervisor-laravel-worker.conf.example /etc/supervisor/conf.d/laravel-worker.conf

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN rm -f bootstrap/cache/*.php 2>/dev/null || true

COPY --from=frontend /frontend/public/build ./public/build

# Create all writable directories, seed SQLite placeholder, and set ownership at build time.
# Named volumes will overlay storage at runtime, but bootstrap/cache and database stay image-resident
# unless bind-mounted — entrypoint re-validates ownership for both cases.
RUN mkdir -p \
        database \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && touch database/database.sqlite \
    && chown -R www-data:www-data database storage bootstrap/cache \
    && chmod -R ug+rwX database storage bootstrap/cache

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint-app.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-app.sh

RUN php artisan package:discover --ansi 2>/dev/null || true

ENTRYPOINT ["/usr/local/bin/docker-entrypoint-app.sh"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx
WORKDIR /var/www/html
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=php /var/www/html/public ./public
