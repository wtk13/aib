ARG PHP_VERSION=8.3.20
FROM php:${PHP_VERSION}-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip libpq-dev libzip-dev oniguruma-dev icu-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev nodejs npm

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    pdo_pgsql pgsql zip mbstring bcmath intl gd opcache

# Redis extension
RUN apk add --no-cache autoconf g++ make \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del autoconf g++ make

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader 2>/dev/null || true
