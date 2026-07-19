# ---------- Stage 1: Composer dependencies ----------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# ---------- Stage 2: Runtime (php-fpm + nginx + supervisor) ----------
FROM php:8.4-fpm-alpine AS runtime

# System packages + PHP extensions required by Laravel
RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        sqlite \
        sqlite-dev \
        libpng-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        bcmath \
        intl \
        zip \
        opcache \
    && apk del .build-deps

WORKDIR /var/www/html

# Application code + vendored dependencies
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Regenerate optimized autoloader now that all source files are present
COPY --from=vendor /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && rm -f /usr/bin/composer

# Container configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-taskora.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Writable directories for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
