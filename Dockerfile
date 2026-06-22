# ── Stage 1: PHP dependencies (нужен для Ziggy в Vite) ────────────────────────
FROM composer:2 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# ── Stage 2: Build frontend assets ────────────────────────────────────────────
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY . .
# Ziggy (vendor/tightenco/ziggy) нужен Vite при сборке
COPY --from=composer /app/vendor ./vendor
RUN npm run build

# ── Stage 3: PHP application ───────────────────────────────────────────────────
FROM php:8.2-fpm-alpine

# install-php-extensions ставит готовые расширения без компиляции из исходников
# (не тянет gcc-тулчейн → сборка минуты вместо ~20)
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
        pdo_mysql mbstring xml bcmath gd zip pcntl

WORKDIR /var/www/html

COPY . .
COPY --from=composer /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Сохраняем public отдельно — entrypoint синхронизирует его в volume
RUN cp -r ./public ./public_static

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
