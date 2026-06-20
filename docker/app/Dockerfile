ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm-alpine

RUN apk add --no-cache \
    git curl zip unzip libpq-dev libzip-dev oniguruma-dev icu-dev icu-data-full \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    nodejs npm

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    pdo_pgsql pgsql zip mbstring bcmath intl gd opcache pcntl

# Redis extension
RUN apk add --no-cache autoconf g++ make \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del autoconf g++ make

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
RUN npm ci && npm run build && rm -rf node_modules
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= php artisan filament:assets --ansi 2>&1 || true
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache
