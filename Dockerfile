# Stage 1: install deps without running scripts
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Stage 2: PHP runtime
FROM php:8.1-cli

# instalar librerías de SO y extensiones necesarias
RUN apt-get update \
 && apt-get install -y libzip-dev zip unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql zip \
 && rm -rf /var/lib/apt/lists/*

# Instalar Composer en la imagen PHP
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# traemos vendor ya instalado y el resto del código
COPY --from=vendor /app/vendor ./vendor
COPY . .

# ahora que PHP y extensiones están, corremos autoload y descubrimos paquetes
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi

EXPOSE ${PORT:-8080}

CMD php artisan migrate --force \
 && php artisan storage:link \
 && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}