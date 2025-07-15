# 1) Build de dependencias con Composer
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# 2) Imagen final de PHP
FROM php:8.1-cli

# Instala dependencias de sistema + dev headers para zip y Postgres
RUN apt-get update \
  && apt-get install -y \
     libzip-dev \
     zip \
     unzip \
     libpq-dev \
  && docker-php-ext-install pdo pdo_pgsql zip

WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY . .

EXPOSE ${PORT:-8080}

CMD php artisan migrate --force \
    && php artisan storage:link \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
