# Product Hub API

Backend do **Product Hub** Aleevia — Laravel 12, API JSON versionada.

## Stack

- PHP 8.2+
- Laravel 12
- SQLite por padrão (local)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

API em `http://localhost:8000`.

## Endpoints

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/v1/health` | Health check |
| GET | `/api/v1/roadmap/state` | Estado completo do roadmap |
| POST | `/api/v1/roadmap/import` | Importação inicial (banco vazio) |
| POST | `/api/v1/roadmap/sync` | Sincronização em lote (publicar) |
| CRUD | `/api/v1/roadmap/items` | Itens do roadmap |
| CRUD | `/api/v1/roadmap/products` | Módulos customizados |

Exemplo:

```bash
curl http://localhost:8000/api/v1/health
```

## Frontend

O app `product_hub_app` usa proxy Vite para `/api` → `localhost:8000`. Configure `FRONTEND_URL` no `.env` quando habilitar CORS restrito.

## Sincronizar roadmap no banco

Estado canônico em `database/roadmap-state.json` (gerado do seed do frontend):

```bash
node scripts/export-roadmap-state.mjs
php artisan serve
node scripts/push-roadmap.mjs
```

Para publicar edições feitas no navegador (localStorage), use `product_hub_app/public/roadmap-sync.html`.

## Deploy no Railway

A API usa SQLite em `/app/database/database.sqlite`. **Sem volume persistente, o banco é apagado a cada redeploy.**

### 1. Volume persistente (obrigatório em produção)

No [Railway Dashboard](https://railway.com):

1. Abra o serviço da API → **Volumes** → **Add Volume**
2. **Mount path:** `/app/database`
3. Redeploy o serviço

Via CLI (após `railway login` e `railway link`):

```bash
railway volume add --mount-path /app/database
```

O entrypoint detecta `RAILWAY_VOLUME_MOUNT_PATH` e grava o SQLite no volume.

### 2. Variáveis de ambiente

| Variável | Valor |
|----------|-------|
| `APP_KEY` | Chave fixa (gere com `php artisan key:generate --show` localmente) |
| `APP_ENV` | `production` |
| `FRONTEND_URL` | `https://product-hub-app-six.vercel.app` |

### 3. Importar roadmap para produção

```bash
node scripts/push-roadmap.mjs database/roadmap-state.json https://producthubapi-production.up.railway.app/api/v1 saas
```

## Branch padrão

`staging` — alinhado ao workflow Aleevia.
