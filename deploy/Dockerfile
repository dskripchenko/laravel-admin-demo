# syntax=docker/dockerfile:1.6
# Multi-stage build для laravel-admin-demo.
#
# Этап 1: composer install (PHP 8.5)
# Этап 2: npm build (Node 20)
# Этап 3: финальный runtime-образ на php:8.5-fpm-alpine с nginx-supervisor.

FROM composer:2 AS composer-stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

FROM node:20-alpine AS node-stage
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund
COPY . .
COPY --from=composer-stage /app/vendor /app/vendor
RUN npm run build

FROM php:8.5-fpm-alpine AS runtime
RUN apk add --no-cache nginx supervisor sqlite \
    && docker-php-ext-install bcmath pdo pdo_mysql opcache \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html
COPY --from=composer-stage /app/vendor ./vendor
COPY --from=node-stage /app/public/build ./public/build
COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY deploy/nginx.conf /etc/nginx/http.d/default.conf
COPY deploy/supervisord.conf /etc/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
