# Taskora

Marketing website for Taskora, split into a **Vue.js frontend** and a **Laravel API backend**, with **PostgreSQL** as the data store.

## Project Structure

```
taskora/
├── backend/              # Laravel API (content + cost estimator)
├── frontend/             # Vue 3 + Vite SPA
├── docker-compose.yml    # Postgres + backend + frontend
└── DEPLOY.md             # CentOS / production Docker guide
```

## Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- PostgreSQL 16+ (or Docker)

## Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

API runs at `http://localhost:8000`.

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/site` | Full site content loaded from PostgreSQL |
| POST | `/api/estimate` | Cost estimator (prices from PostgreSQL) |

**Estimate request body:**
```json
{
  "service_id": "deep_cleaning",
  "frequency_id": "one_time",
  "size": 5
}
```

## Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```

Frontend runs at `http://localhost:5173` and proxies `/api` to Laravel.

## Production (Docker)

```bash
cp .env.example .env
docker compose up -d --build
```

See [DEPLOY.md](DEPLOY.md) for CentOS 10 Stream instructions.
