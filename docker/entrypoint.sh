#!/bin/sh
set -e
cd /app

# Railway injeta a porta via $PORT
: "${PORT:=8080}"
: "${DB_CONNECTION:=sqlite}"

# Persistência: monte um volume Railway em /app/database (ver README).
# Sem volume, os dados são apagados a cada redeploy.
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_DIR="${RAILWAY_VOLUME_MOUNT_PATH:-/app/database}"
    mkdir -p "$DB_DIR"
    export DB_DATABASE="${DB_DATABASE:-$DB_DIR/database.sqlite}"
    touch "$DB_DATABASE"
fi

# se APP_KEY não veio por env var, gera uma (defina APP_KEY no Railway p/ estabilidade)
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# `php artisan serve` NÃO repassa aos workers HTTP as env vars que não estão no .env
# (ex.: DB_URL do Railway) — só as declaradas nele. Cachear a config aqui, no processo
# com o env completo, congela DB_CONNECTION/DB_URL p/ todos os workers.
php artisan config:cache

php artisan migrate --force || true

# materializa o roadmap-state.json no banco (idempotente: ignora se já houver dados)
php artisan db:seed --class=RoadmapSeeder --force || true

exec php artisan serve --host 0.0.0.0 --port "$PORT"
