# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1: Build frontend assets with Vite
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* .npmrc* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2: PHP application (Apache + mod_php)
# ---------------------------------------------------------------------------
FROM php:8.5-apache AS app

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1

# System dependencies and PHP extensions required by Laravel + PostgreSQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql zip intl bcmath pcntl \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

# Production PHP settings + OPcache
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > "$PHP_INI_DIR/conf.d/opcache.ini"

# Point Apache at Laravel's public/ directory and enable pretty URLs
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first to take advantage of layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# Application source and compiled assets
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

# Entrypoint: prepare .env and APP_KEY at runtime (never bake the key into the image)
COPY <<'EOF' /usr/local/bin/docker-entrypoint.sh
#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
    sed -i -e 's/^APP_ENV=.*/APP_ENV=production/' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
fi

if [ -z "$APP_KEY" ] && ! grep -q '^APP_KEY=.\+' .env; then
    echo "WARNING: APP_KEY not provided, generating one. Set APP_KEY as an environment variable to keep it stable across deploys." >&2
    php artisan key:generate --force --no-interaction
fi

chown www-data:www-data .env

# Cache config/routes/views at startup so runtime env vars are picked up
php artisan optimize

exec "$@"
EOF
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
