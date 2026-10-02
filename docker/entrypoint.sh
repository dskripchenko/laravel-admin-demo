#!/bin/sh
# Prepares the stand on every start: an application key that survives
# restarts, the cached config, and fresh demo data.
set -e
cd /app

if [ -z "$APP_KEY" ]; then
    if [ ! -f /data/app.key ]; then
        php -r 'echo "base64:".base64_encode(random_bytes(32));' > /data/app.key
    fi
    APP_KEY="$(cat /data/app.key)"
    export APP_KEY
fi

touch /data/database.sqlite
php artisan optimize --ansi
# Every start is a reset: the stand always begins from the seed.
php artisan demo:reset --force --ansi

exec "$@"
