#!/bin/bash
set -e

cd /var/www/html

# Provision an environment file on first boot
if [ ! -f .env ]; then
    cp .env.docker .env
fi

# Overlay DB settings from the container environment (docker-compose)
if [ -n "${DB_CONNECTION:-}" ]; then
    sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=${DB_CONNECTION}|" .env
fi
if [ -n "${DB_HOST:-}" ]; then
    sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env
fi
if [ -n "${DB_PORT:-}" ]; then
    sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env
fi
if [ -n "${DB_DATABASE:-}" ]; then
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
fi
if [ -n "${DB_USERNAME:-}" ]; then
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
fi
if [ -n "${DB_PASSWORD:-}" ]; then
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
fi
if [ -n "${APP_URL:-}" ]; then
    sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
fi
if [ -n "${FRONTEND_URL:-}" ]; then
    sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${FRONTEND_URL}|" .env
fi

# Generate an application key if one is not present
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Make sure runtime directories are writable by php-fpm/nginx
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Rebuild the package manifest (composer scripts are skipped during build)
php artisan package:discover --ansi || true

# Wait for PostgreSQL to accept connections
echo "Waiting for PostgreSQL..."
for i in $(seq 1 30); do
    if php -r "
        try {
            new PDO(
                sprintf('pgsql:host=%s;port=%s;dbname=%s', getenv('DB_HOST') ?: 'postgres', getenv('DB_PORT') ?: '5432', getenv('DB_DATABASE') ?: 'taskora'),
                getenv('DB_USERNAME') ?: 'taskora',
                getenv('DB_PASSWORD') ?: 'taskora_secret'
            );
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    "; then
        echo "PostgreSQL is ready."
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "PostgreSQL did not become ready in time." >&2
        exit 1
    fi
    sleep 2
done

# Apply migrations and seed site content
php artisan migrate --force
php artisan db:seed --force

# Cache configuration for production performance
php artisan config:cache
php artisan view:cache || true

# ------------------------------------------------------------------
# TLS / nginx server configuration
# ------------------------------------------------------------------
ENABLE_SSL="${ENABLE_SSL:-false}"
DOMAIN="${DOMAIN:-}"
CERTBOT_EMAIL="${CERTBOT_EMAIL:-}"
CERTBOT_STAGING="${CERTBOT_STAGING:-false}"
NGINX_CONF="/etc/nginx/conf.d/taskora.conf"

mkdir -p /var/www/certbot /etc/nginx/conf.d

write_http_only_conf() {
    cat > "$NGINX_CONF" <<EOF
server {
    listen 80;
    server_name ${DOMAIN:-_};

    location ^~ /.well-known/acme-challenge/ {
        root /var/www/certbot;
        default_type "text/plain";
    }

    include /etc/nginx/snippets/app-locations.conf;
}
EOF
}

write_ssl_conf() {
    local cert="$1"
    local key="$2"
    cat > "$NGINX_CONF" <<EOF
server {
    listen 80;
    server_name ${DOMAIN};

    location ^~ /.well-known/acme-challenge/ {
        root /var/www/certbot;
        default_type "text/plain";
    }

    location / {
        return 301 https://\$host\$request_uri;
    }
}

server {
    listen 443 ssl;
    http2 on;
    server_name ${DOMAIN};

    ssl_certificate ${cert};
    ssl_certificate_key ${key};
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;

    include /etc/nginx/snippets/app-locations.conf;
}
EOF
}

if [ "$ENABLE_SSL" = "true" ] && [ -n "$DOMAIN" ] && [ -n "$CERTBOT_EMAIL" ]; then
    echo "SSL enabled for domain: ${DOMAIN}"
    LIVE_DIR="/etc/letsencrypt/live/${DOMAIN}"

    # Obtain a certificate on first boot. Port 80 is free here because
    # nginx has not started yet, so we can use the standalone authenticator.
    if [ ! -f "${LIVE_DIR}/fullchain.pem" ]; then
        STAGING_FLAG=""
        [ "$CERTBOT_STAGING" = "true" ] && STAGING_FLAG="--staging"
        echo "Requesting Let's Encrypt certificate (standalone)..."
        timeout 120 certbot certonly --standalone --non-interactive --agree-tos \
            --email "$CERTBOT_EMAIL" -d "$DOMAIN" $STAGING_FLAG \
            --http-01-port 80 \
            || echo "WARNING: certbot issuance failed; falling back to a self-signed certificate."
    fi

    if [ -f "${LIVE_DIR}/fullchain.pem" ]; then
        echo "Using Let's Encrypt certificate."
        write_ssl_conf "${LIVE_DIR}/fullchain.pem" "${LIVE_DIR}/privkey.pem"
    else
        # Self-signed fallback so nginx can still serve HTTPS until DNS/issuance works.
        mkdir -p /etc/nginx/ssl
        if [ ! -f /etc/nginx/ssl/selfsigned.crt ]; then
            openssl req -x509 -nodes -newkey rsa:2048 -days 365 \
                -keyout /etc/nginx/ssl/selfsigned.key \
                -out /etc/nginx/ssl/selfsigned.crt \
                -subj "/CN=${DOMAIN}" >/dev/null 2>&1
        fi
        echo "Using self-signed certificate (temporary)."
        write_ssl_conf /etc/nginx/ssl/selfsigned.crt /etc/nginx/ssl/selfsigned.key
    fi
else
    echo "SSL disabled — serving over HTTP only."
    write_http_only_conf
fi

exec "$@"
