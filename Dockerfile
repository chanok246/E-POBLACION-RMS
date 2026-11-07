# e-Poblacion RMS - Laravel 12 / PHP 8.2 / Apache
FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1

# System libraries and PHP extensions.
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        libxml2-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        dom \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        xml \
        zip \
    && a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/000-default.conf \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install locked production dependencies.
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

# Copy application source.
# .dockerignore must exclude .env, local databases, resident records and uploads.
COPY . .

# Prepare Laravel writable directories before package discovery.
RUN mkdir -p \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer dump-autoload \
        --no-dev \
        --optimize \
        --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Render supplies PORT. Configure Apache to listen on that port.
CMD ["bash", "-lc", "set -e; PORT=\"${PORT:-10000}\"; sed -ri \"s/^Listen 80$/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -ri \"s#<VirtualHost [^>]+>#<VirtualHost *:${PORT}>#\" /etc/apache2/sites-available/000-default.conf; apache2-foreground"]
