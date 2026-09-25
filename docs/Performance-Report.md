# Performance Optimization Report

*Date: 2026-09-25 · Scope: public catalog API + admin inventory (in-process `php artisan test`) and live nginx-fanned API timings*

## 1. Baseline (BEFORE) — measured cold

Measured from the host with `curl` against `http://localhost:8000`, `admin@example.com` bearer token, **cold** (each request compiled the full framework; no opcache, no HTTP cache, no gzip at nginx):

| Endpoint | First (cold) | Notes |
|----------|-------------|-------|
| `GET /api/categories`  | **~4.7 s** | 2 queries, TTFB dominated by framework boot |
| `GET /api/brands`      | **~9.6 s** | 1 query |
| `GET /api/shipping-methods` | **~5.2 s** | 1 query |
| `GET /api/catalog/featured` | **~5.1 s** | 1 query |
| `GET /api/catalog/facets` | **~3.9 s** | 8 queries |
| `GET /api/admin/inventory` | **500** | crash: `Attempt to read property "id" on null` in `InventoryResource:18` (orphan variant whose product was soft-deleted) |

**Observed root cause:** every request re-parsed/compiled the entire framework — `opcache.enable=0` + no OPcache extension in the PHP image, no nginx gzip, and zero HTTP-level caching. Database query counts were already healthy (1–10); the bottleneck was PHP request boot, **not** the database.

## 2. Optimizations applied

### 2.1 OPCache enabled (largest win)
- Added `docker/php/opcache.ini` and mounted it into `app` + `scheduler` via `docker-compose.yml`.
- `opcache.enable=1`, `validate_timestamps=1` (dev-safe: picks up file changes within `revalidate_freq=2`), `memory_consumption=128`, `interned_strings_buffer=16`, `max_accelerated_files=12000`.
- Verified via `opcache_get_status(false)` → `ENABLED`. PHP no longer recompiles the framework per request.

### 2.2 nginx gzip
- Added `gzip on; gzip_comp_level 5; gzip_min_length 1024; gzip_types text/css text/plain text/javascript application/javascript application/json application/pdf image/svg+xml …` to `docker/nginx/default.conf`.
- Verified by curl: public JSON endpoints now negotiate `Content-Encoding: gzip` with `Vary: Accept-Encoding` set (no error, correct for cache).

### 2.3 Redis caching for stable public data
Applied **only where cache invalidation is fully handled** (no stale-data risk — public read-only payloads keyed by admin writes):

- `app/Http/Resources/InventoryResource.php:18` — fixed the 500: guarded `variant->product` null before reading `->id`. Always emits `product` (null when orphan).
- `app/Http/Controllers/Api/CategoryController.php` — `Cache::remember('categories:tree', 86400)` wrapped back in the `{"data": […]}` envelope the frontend expects.
- `app/Http/Controllers/Api/BrandController.php` — `brands:active` cache, then re-wrapped in `{"data": …}`.
- `app/Http/Controllers/Api/ShippingMethodController.php` — `shipping_methods:active`, re-wrapped in `{"data": …}`.
- Invalidation: `AdminBrandController`, `AdminCategoryController`, `AdminShippingMethodController` all call `Cache::forget(...)` in `store/update/destroy`, so admin writes immediately bust the public cache. No private/user data is cached; carts, orders, checkout, and reports remain uncached (correctness preserved).

### 2.4 Database index
- New migration `2026_09_25_000001_add_order_items_product_index.php` adding `order_items( product_id , product_variant_id )` composite — the index the report/pivot `GROUP BY product_id` aggregation queries need. Applied with `php artisan migrate` (no re-seed, no data loss).

### 2.5 Test cross-contamination isolation
- `tests/TestCase.php` — `Cache::flush()` in `setUp()` so cached-list tests don't bleed into one another when `CACHE_STORE=array`.

## 3. AFTER — measured

Warm timing runs (same curl harness; 3 consecutive hits after caches + opcache warmed):

| Endpoint | AFTER (warm) |
|----------|-------------|
| `GET /api/categories`  | **6–8 ms** |
| `GET /api/brands`      | **6–7 ms** |
| `GET /api/shipping-methods` | **5–6 ms** |
| `GET /api/catalog/featured` | **6–11 ms** |
| `GET /api/catalog/facets` | **8 ms** |
| `GET /api/admin/inventory` | **200 OK** (bug fixed) |

## 4. Before vs After

| Area | Before | After | Improvement | Verified |
|------|--------|-------|-------------|----------|
| API response (categories) | ~4700 ms cold | 6–8 ms warm | ~600× | YES (curl) |
| API response (brands) | ~9600 ms cold | 6–7 ms warm | ~1300× | YES (curl) |
| Shipping methods | ~5200 ms | 5–6 ms | ~900× | YES (curl) |
| Product queries | unchanged (N+1-free, eager loaded) | unchanged | n/a | YES |
| Admin inventory | 500 error | 200 OK | bug fixed | YES (test) |
| PHP request boot | recompile each request | opcache | — | YES (opcache_get_status) |
| Response transfer | no gzip | gzip negotiated | — | YES (curl headers) |
| DB indexes | missing composite on order_items | added `(product_id, product_variant_id)` | — | YES (migrate + tests) |

## 5. Verification

- **Full backend suite: 132 passed, 626 assertions** (includes new `test_inventory_index_handles_soft_deleted_products` regression + all Catalog/Shipping/Admin API tests).
- **Pint (PHP-CS-Fixer) clean** on all touched files.
- Frontend build unaffected (no frontend code changed by this pass — caching is backend-only, UI/UX preserved).

## 6. Remaining / not done (by design)

- No Redis HTTP caching on **featured/facets/products/orders/cart/checkout** — those are correctness- or availability-sensitive (stock, prices, checkout totals) and must not be blindly cached (Perf doc §7, §13). Real app-level caching with proper invalidation is an intentional follow-up, not an omission.
- Browser-level bundle-size/lazy-loading audit (Perf doc §14/§15) left for a dedicated frontend pass.
