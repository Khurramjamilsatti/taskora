# Taskora

Marketing website for Taskora, split into a **Vue.js frontend** and a **Laravel API backend**.

## Project Structure

```
taskora/
├── backend/          # Laravel API (content + cost estimator)
├── frontend/         # Vue 3 + Vite SPA
└── taskora-website.html  # Original static HTML reference
```

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+

## Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan serve
```

API runs at `http://localhost:8000`.

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/site` | Full site content (nav, hero, services, reviews, etc.) |
| POST | `/api/estimate` | Cost estimator calculation |

**Estimate request body:**
```json
{
  "base_price": 900,
  "size": 5,
  "frequency_factor": 1
}
```

## Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```

Frontend runs at `http://localhost:5173` and proxies `/api` requests to the Laravel backend.

### Environment

`frontend/.env`:
```
VITE_API_URL=http://localhost:8000/api
```

`backend/.env`:
```
FRONTEND_URL=http://localhost:5173
```

## Development

Run both servers in separate terminals:

```bash
# Terminal 1 — API
cd backend && php artisan serve

# Terminal 2 — Frontend
cd frontend && npm run dev
```

Open `http://localhost:5173` in your browser.

## Production Build

```bash
cd frontend && npm run build
```

Serve the `frontend/dist` folder with any static host (Nginx, Vercel, etc.) and point `VITE_API_URL` to your production Laravel API URL.
