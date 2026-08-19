# Product Hub API — Laravel 12 (Railway)
# Build determinístico, sem etapa de npm/vite (a API não serve assets compilados).
FROM php:8.4-cli-alpine

# Extensões PHP exigidas pelo Laravel 12 + drivers sqlite (local/testes), pgsql (Railway)
# e gd (phpoffice/phpspreadsheet, usado na importação de planilhas)
RUN apk add --no-cache \
        git unzip libzip-dev icu-dev oniguruma-dev sqlite sqlite-dev postgresql-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql mbstring zip bcmath pcntl intl gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# .env precisa existir antes do composer install (scripts pós-autoload bootam o app)
COPY . .
RUN cp -n .env.example .env \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs database \
    && chmod -R 775 storage bootstrap/cache

# DB definido por variáveis do Railway (DB_CONNECTION=pgsql + DB_URL).
# Sem elas, o config cai no default sqlite (efêmero) — só p/ bootstrap/health.
ENV APP_ENV=production \
    APP_DEBUG=false

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8080
CMD ["entrypoint"]
