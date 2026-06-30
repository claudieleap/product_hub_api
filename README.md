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

## Branch padrão

`staging` — alinhado ao workflow Aleevia.
