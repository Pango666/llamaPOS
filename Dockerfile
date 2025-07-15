# 1) Build dependencies stage
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
# Instala las dependencias y ejecuta scripts (incluye package discovery)
RUN composer install --no-dev --optimize-autoloader

# 2) PHP runtime
FROM php:8.1-cli

# Instala las librerías de sistema para zip y PostgreSQL
RUN apt-get update \
    && apt-get install -y libzip-dev zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
# Copia la carpeta vendor del stage anterior y el código de la aplicación
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Expone el puerto asignado por Render
EXPOSE ${PORT:-8080}

# Ejecuta migraciones, vincula storage y arranca el servidor
CMD ["sh", "-c", "php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
