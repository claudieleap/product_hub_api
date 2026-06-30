#!/bin/sh
set -e
cd /app

# Railway injeta a porta via $PORT
: "${PORT:=8080}"

# garante o arquivo do sqlite (banco efêmero — ok p/ health/bootstrap)
touch "${DB_DATABASE:-/app/database/database.sqlite}"

# se APP_KEY não veio por env var, gera uma (defina APP_KEY no Railway p/ estabilidade)
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force || true

exec php artisan serve --host 0.0.0.0 --port "$PORT"
