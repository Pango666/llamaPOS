# 1) Build de dependencias con Composer
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# 2) Imagen final de PHP
FROM php:8.1-cli

RUN apt-get update \
  && apt-get install -y libzip-dev zip unzip \
  && docker-php-ext-install pdo pdo_pgsql zip

WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Expone el puerto que Render asigna
EXPOSE ${PORT:-8080}

# Arranque: corre migraciones, storage link y el servidor embebido
CMD php artisan migrate --force \
    && php artisan storage:link \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
