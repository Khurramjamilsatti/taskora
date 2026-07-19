#!/bin/bash
# Certbot auto-renewal loop. Runs under supervisor.
# Renews via the nginx webroot and reloads nginx on success.

set -u

if [ "${ENABLE_SSL:-false}" != "true" ]; then
    # SSL disabled — stay idle so supervisor keeps the program "running".
    while true; do
        sleep 86400
    done
fi

# Give nginx a moment to come up before the first renewal attempt.
sleep 30

while true; do
    certbot renew \
        --webroot -w /var/www/certbot \
        --quiet \
        --deploy-hook "nginx -s reload" || true
    # Check twice a day, as recommended by Let's Encrypt.
    sleep 43200
done
