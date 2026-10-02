# The demo stand in one container: FrankenPHP serves the app, supervisord
# keeps the scheduler (hourly demo:reset, health checks, telemetry) and a
# queue worker running next to it. SQLite lives on the /data volume.
FROM dunglas/frankenphp:1-php8.4-bookworm

RUN install-php-extensions pdo_sqlite gd intl zip opcache pcntl \
    && apt-get update \
    && apt-get install -y --no-install-recommends supervisor curl unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-demo.ini

WORKDIR /app

COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && php artisan admin:publish \
    && mkdir -p /data storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && rm -f public/storage

COPY docker/supervisord.conf /etc/supervisor/conf.d/demo.conf
COPY docker/entrypoint.sh /usr/local/bin/demo-entrypoint
RUN chmod +x /usr/local/bin/demo-entrypoint

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    ADMIN_DEMO=true \
    SERVER_NAME=:8080

VOLUME /data
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["demo-entrypoint"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
