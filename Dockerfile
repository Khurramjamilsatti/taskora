# ============================================================
# Taskora — single image running BOTH the Vue frontend and the
# Laravel backend (nginx + php-fpm + supervisor). PostgreSQL is
# provided as a separate service via docker-compose.
# ============================================================

# ---------- Stage 1: Build the Vue frontend ----------
FROM node:20-alpine AS frontend

WORKDIR /app
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY frontend/ ./

# Same-origin API: nginx proxies /api to Laravel in the same container
ENV VITE_API_URL=/api
RUN npm run build

# ---------- Stage 2: Composer dependencies ----------
FROM composer:2 AS vendor

WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# ---------- Stage 3: Runtime (nginx + php-fpm + supervisor) ----------
FROM php:8.4-fpm-alpine AS runtime

# System packages + PHP extensions required by Laravel (PostgreSQL)
# Includes certbot + openssl for TLS certificate issuance/renewal.
RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        certbot \
        openssl \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        postgresql-dev \
        libpng-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        bcmath \
        intl \
        zip \
        opcache \
    && apk del .build-deps \
    && mkdir -p /etc/nginx/conf.d /etc/nginx/snippets /var/www/certbot

WORKDIR /var/www/html

# Laravel application code + vendored dependencies
COPY backend/ .
COPY --from=vendor /app/vendor ./vendor

# Regenerate optimized autoloader now that all source files are present
COPY --from=vendor /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && rm -f /usr/bin/composer

# Compiled Vue SPA served as static files by nginx
COPY --from=frontend /app/dist /var/www/frontend

# Container configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/app-locations.conf /etc/nginx/snippets/app-locations.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-taskora.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/certbot-renew.sh /usr/local/bin/certbot-renew
RUN chmod +x /usr/local/bin/entrypoint /usr/local/bin/certbot-renew

# Writable directories for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80 443

ENTRYPOINT ["entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
