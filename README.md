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
| GET | `/api/v1/health` | Health check para o frontend |
| GET | `/up` | Health do framework Laravel |

Exemplo:

```bash
curl http://localhost:8000/api/v1/health
```

## Frontend

O app `product_hub_app` usa proxy Vite para `/api` → `localhost:8000`. Configure `FRONTEND_URL` no `.env` quando habilitar CORS restrito.

## Branch padrão

`staging` — alinhado ao workflow Aleevia.
