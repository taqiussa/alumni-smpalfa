########################
# Stage 1 — Production PHP dependencies
# Built first because Flux CSS is resolved by Vite from vendor/.
########################
FROM serversideup/php:8.4-cli AS vendor_builder

USER root
WORKDIR /var/www/html

RUN install-php-extensions gd pdo_mysql

COPY --chown=www-data:www-data composer.json composer.lock ./
COPY --chown=www-data:www-data app ./app

USER www-data

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --classmap-authoritative \
    --optimize-autoloader

########################
# Stage 2 — Frontend assets
########################
FROM node:24-alpine AS frontend_builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
COPY --from=vendor_builder /var/www/html/vendor ./vendor
RUN npm run build

########################
# Stage 3 — Runtime
########################
FROM serversideup/php:8.4-frankenphp

USER root
WORKDIR /var/www/html

RUN install-php-extensions gd pdo_mysql

COPY --chown=www-data:www-data --from=vendor_builder /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=frontend_builder /app/public/build ./public/build

RUN mkdir -p \
    storage/app/livewire-tmp \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

HEALTHCHECK \
    --interval=30s \
    --timeout=5s \
    --start-period=60s \
    --retries=3 \
    CMD php -r '$context = stream_context_create(["http" => ["ignore_errors" => true, "timeout" => 4]]); @file_get_contents("http://127.0.0.1:8080/up", false, $context); $status = $http_response_header[0] ?? ""; exit((int) ! preg_match("#\\s200(?:\\s|$)#", $status));'

USER www-data

RUN php artisan package:discover --ansi
