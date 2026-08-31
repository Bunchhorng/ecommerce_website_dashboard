# E-Commerce System

A full-stack E-Commerce Management System built with a **Laravel REST API**, **Vue 3 + TypeScript** frontend, **MySQL 8**, **Redis**, **Nginx**, and **Docker**.

This platform provides a complete customer storefront (browsing, cart, checkout, orders, wishlist, reviews) and a full-featured admin dashboard (catalog, inventory, orders, coupons, reviews, reports).

## 📚 Documentation

The full documentation suite lives in the [`docs/`](./docs) directory:

| Document | Description |
| --- | --- |
| [`docs/ARCHITECTURE.md`](./docs/ARCHITECTURE.md) | System architecture, database schema, relationships, and business-logic engine |
| [`docs/API.md`](./docs/API.md) | REST API reference: endpoints, auth, resources, services, and request/response shapes |
| [`docs/FRONTEND.md`](./docs/FRONTEND.md) | Vue 3 frontend: project layout, views, routing, state (Pinia), API client, i18n |
| [`docs/DEVELOPMENT.md`](./docs/DEVELOPMENT.md) | Docker workflow, common commands, git conventions, and troubleshooting |

## 🛠️ Technology Stack

**Backend**
- Laravel (PHP 8.3)
- Laravel Sanctum (token auth)
- MySQL 8
- Redis (cache / session / queue)
- Nginx

**Frontend**
- Vue 3 (Composition API, `<script setup>`)
- TypeScript
- Vite
- Vue Router
- Pinia
- Axios
- Bootstrap 5
- SweetAlert2
- Recharts / Chart.js

**DevOps**
- Docker
- Docker Compose

## 📁 Project Structure

```
E-Ecommerce/
├── backend/            # Laravel REST API
│   ├── app/
│   │   ├── Http/Controllers/Api         # Public + customer API controllers
│   │   ├── Http/Controllers/Api/Admin   # Admin API controllers
│   │   ├── Http/Requests                # Form requests (validation)
│   │   ├── Http/Resources               # API resources (responses)
│   │   ├── Http/Middleware              # e.g. admin middleware
│   │   ├── Models                       # Eloquent models
│   │   └── Services                     # Business logic (cart, checkout, inventory, ...)
│   ├── database/migrations              # Full e-commerce schema
│   └── routes/api.php                   # API route definitions
│
├── frontend/           # Vue 3 + TypeScript
│   ├── src/
│   │   ├── api/client.ts                # Axios instance + interceptors
│   │   ├── components/                  # Shared UI + admin components
│   │   ├── layouts/                     # Store / Account / Admin layouts
│   │   ├── router/                      # Vue Router config
│   │   ├── stores/                      # Pinia stores (cart, wishlist, ui)
│   │   ├── views/                       # Page components
│   │   └── locales/                     # i18n (en, km)
│   └── vite.config.ts
│
├── docker/
│   ├── nginx/default.conf
│   └── php/Dockerfile
│
├── docker-compose.yml
└── README.md
```

## 🏗️ System Architecture

```
                          E-COMMERCE SYSTEM
                                 │
               ┌─────────────────┴─────────────────┐
               │                                   │
               ▼                                   ▼
        ┌──────────────┐                    ┌──────────────┐
        │  Vue 3       │                    │   Laravel    │
        │  Frontend    │◄────── API ───────►│   Backend    │
        │  :5173       │                    │   :8000      │
        └──────────────┘                    └──────┬───────┘
                                                   │
                                  ┌────────────────┼────────────────┐
                                  │                │                │
                                  ▼                ▼                ▼
                             ┌─────────┐      ┌─────────┐      ┌─────────┐
                             │  MySQL  │      │  Redis  │      │  Storage│
                             │  :3307  │      │  :6379  │      │         │
                             └─────────┘      └─────────┘      └─────────┘
                                  │
                                  ▼
                             phpMyAdmin
                               :8080
```

## 🚀 Quick Start

> **Important:** Always run PHP, Composer, and npm through the Docker containers — never on the host machine.

```bash
# 1. Start the environment
docker compose up -d --build

# 2. Install backend dependencies
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 3. Run migrations + seed data
docker compose exec app php artisan migrate --seed

# 4. Create the storage link (required for product/brand images)
docker compose exec app php artisan storage:link

# 5. Install frontend dependencies (or let the frontend container do it)
docker compose exec frontend npm install
```

### Application URLs

| Service | URL |
| --- | --- |
| Frontend (Vue) | http://localhost:5173 |
| Laravel API | http://localhost:8000 |
| API Base URL | http://localhost:8000/api |
| phpMyAdmin | http://localhost:8080 |

### Daily workflow

```bash
docker compose up -d     # start
docker compose down      # stop
docker compose logs -f   # view logs
```

## 📦 Feature Overview

**Customer Website**
- Auth: login, registration, password change
- Browsing: home, shop (with filters/sorting), product details (variant selector, gallery, stock badges)
- Shopping: cart, multi-step checkout, order success
- Engagement: order history & tracking, wishlist, reviews

**Admin Dashboard**
- KPI overview, revenue charts, recent orders, low-stock alerts
- Catalog: product/variant CRUD, categories, brands
- Commerce: orders pipeline, coupons, review moderation, shipping methods, customers, reports (CSV/PDF)

See [`docs/`](./docs) for full details on architecture, API, and frontend.

## 🔒 Security

- Sensitive environment files (`.env`, `backend/.env`, `frontend/.env`) are untracked.
- Never commit database passwords, API keys, payment secrets, `APP_KEY`, or production credentials.
- API uses Laravel Sanctum bearer-token auth with role-based access (customer vs. admin).

## License

This project is developed for educational and academic purposes.
