#!/bin/bash
set -e

cd /var/www/html

# Provision an environment file on first boot
if [ ! -f .env ]; then
    cp .env.docker .env
fi

# Generate an application key if one is not present
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Ensure the sqlite database file exists (kept on the persisted storage volume)
if [ ! -f storage/database.sqlite ]; then
    touch storage/database.sqlite
fi

# Make sure runtime directories are writable by php-fpm/nginx
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Rebuild the package manifest (composer scripts are skipped during build)
php artisan package:discover --ansi || true

# Apply database migrations (won't fail the boot if the DB is unreachable)
php artisan migrate --force --graceful || true

# Cache configuration for production performance
php artisan config:cache
php artisan view:cache || true

exec "$@"
