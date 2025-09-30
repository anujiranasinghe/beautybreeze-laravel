# syntax=docker/dockerfile:1.6

# -------- Base images --------
FROM composer:2.7 AS composer
FROM node:20-alpine AS node

# -------- Build stage: vendor + assets + caches --------
FROM php:8.3-fpm-alpine AS build

RUN apk add --no-cache bash git zip unzip icu-dev oniguruma-dev libpng-dev libjpeg-turbo-dev libwebp-dev libzip-dev zlib-dev libxml2-dev curl-dev

# PHP extensions commonly used by Laravel
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) gd intl mbstring pdo pdo_mysql opcache zip bcmath

# Copy Composer and Node from official images
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
 && ln -s /usr/local/lib/node_modules/corepack/dist/corepack.js /usr/local/bin/corepack || true

# Set workdir to Laravel app directory
WORKDIR /var/www/html

# Copy only dependency manifests first for better layer caching
COPY example-app/composer.json example-app/composer.lock ./

# Install composer dependencies (no dev; optimized)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Copy app code
COPY example-app/ .

# Install Node deps and build assets (Vite/Tailwind)
RUN --mount=type=cache,target=/root/.npm \
    --mount=type=cache,target=/var/www/html/node_modules \
    if [ -f package-lock.json ] || [ -f npm-shrinkwrap.json ]; then npm ci; else npm install; fi \
    && npm run build || echo "[WARN] npm build skipped (no build script?)"

# Laravel caches (won't include APP_KEY at build; done again on start)
RUN php artisan route:clear || true \
    && php artisan config:clear || true \
    && php artisan view:clear || true \
    && php artisan optimize || true

# -------- Runtime stage: php-fpm + nginx via supervisord --------
FROM alpine:3.20 AS runtime

RUN apk add --no-cache bash nginx supervisor curl icu-libs libpng libjpeg-turbo libwebp zlib libzip oniguruma \
    php83 php83-fpm php83-opcache php83-session php83-xml php83-ctype php83-tokenizer php83-dom php83-fileinfo \
    php83-gd php83-intl php83-mbstring php83-pdo php83-pdo_mysql php83-pgsql php83-pdo_pgsql php83-simplexml \
    php83-zip php83-curl php83-bcmath

# Unify php-fpm path
RUN ln -sf /usr/bin/php83 /usr/bin/php && ln -sf /usr/sbin/php-fpm83 /usr/sbin/php-fpm

WORKDIR /var/www/html

# Copy built app from build stage
COPY --from=build /var/www/html /var/www/html

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Supervisor config
RUN mkdir -p /etc/supervisor.d
RUN printf "%s\n" \
"[supervisord]" \
"nodaemon=true" \
"" \
"[program:php-fpm]" \
"command=/usr/sbin/php-fpm -F" \
"autostart=true" \
"autorestart=true" \
"priority=5" \
"" \
"[program:nginx]" \
"command=/usr/sbin/nginx -g 'daemon off;'" \
"autostart=true" \
"autorestart=true" \
"priority=10" \
"" \
"[program:queue]" \
"directory=/var/www/html" \
"command=/usr/bin/php /var/www/html/artisan queue:work --tries=3 --sleep=1" \
"autostart=true" \
"autorestart=true" \
"priority=15" \
"" \
"[program:scheduler]" \
"directory=/var/www/html" \
"command=/usr/bin/php /var/www/html/artisan schedule:work" \
"autostart=true" \
"autorestart=true" \
"priority=20" \
> /etc/supervisor.d/supervisord.ini


# Fix permissions for storage and bootstrap cache
RUN adduser -D -H -u 1000 -s /bin/sh www \
 && chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_FPM_LISTEN=127.0.0.1:9000

# Healthcheck — try to hit the homepage
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD wget -q -O /dev/null http://127.0.0.1/ || exit 1

# Entrypoint: storage link and caches, then start supervisord
COPY --link <<'EOF' /usr/local/bin/entrypoint.sh
#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Ensure symlink exists (idempotent)
php -v >/dev/null 2>&1 || true
if [ -d storage ] && [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

# Cache config/routes/views at runtime (APP_KEY must be present)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec /usr/bin/supervisord -c /etc/supervisor.d/supervisord.ini
EOF

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]

