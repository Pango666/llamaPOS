# 1) Build dependencies stage
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
# Install PHP deps without running scripts (avoids missing artisan)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 2) PHP runtime
FROM php:8.1-cli

# Install system libs for zip and Postgres
RUN apt-get update \
    && apt-get install -y libzip-dev zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

WORKDIR /app
# Copy vendor from previous stage and application code
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Re-generate autoload and run Laravel package discovery
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Expose the port that Render will map
EXPOSE ${PORT:-8080}

# Final command: migrate, link storage and serve
CMD ["sh", "-c", "php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
