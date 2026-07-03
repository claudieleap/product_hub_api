#!/bin/sh
set -e
cd /app

# Railway injeta a porta via $PORT
: "${PORT:=8080}"

# Persistência: monte um volume Railway em /app/database (ver README).
# Sem volume, os dados são apagados a cada redeploy.
DB_DIR="${RAILWAY_VOLUME_MOUNT_PATH:-/app/database}"
mkdir -p "$DB_DIR"
export DB_DATABASE="${DB_DATABASE:-$DB_DIR/database.sqlite}"
touch "$DB_DATABASE"

# se APP_KEY não veio por env var, gera uma (defina APP_KEY no Railway p/ estabilidade)
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force || true

exec php artisan serve --host 0.0.0.0 --port "$PORT"
