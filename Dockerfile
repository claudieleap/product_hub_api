# Product Hub API — Laravel 12 (Railway)
# Build determinístico, sem etapa de npm/vite (a API não serve assets compilados).
FROM php:8.4-cli-alpine

# Extensões PHP exigidas pelo Laravel 12 + driver sqlite
RUN apk add --no-cache \
        git unzip libzip-dev icu-dev oniguruma-dev sqlite sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip bcmath pcntl intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# .env precisa existir antes do composer install (scripts pós-autoload bootam o app)
COPY . .
RUN cp -n .env.example .env \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs database \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/app/database/database.sqlite

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8080
CMD ["entrypoint"]
