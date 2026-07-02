#!/bin/sh
set -e
cd /app

# Railway injeta a porta via $PORT
: "${PORT:=8080}"
: "${DB_CONNECTION:=sqlite}"

# arquivo do sqlite só faz sentido no fallback local/efêmero
if [ "$DB_CONNECTION" = "sqlite" ]; then
    touch "${DB_DATABASE:-/app/database/database.sqlite}"
fi

# se APP_KEY não veio por env var, gera uma (defina APP_KEY no Railway p/ estabilidade)
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force || true

# materializa o roadmap-state.json no banco (idempotente: ignora se já houver dados)
php artisan db:seed --class=RoadmapSeeder --force || true

exec php artisan serve --host 0.0.0.0 --port "$PORT"
