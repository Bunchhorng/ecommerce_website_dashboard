You are a Senior Performance Engineer and Full-Stack Architect.

Your task is to ANALYZE, MEASURE, OPTIMIZE, and VERIFY the performance of this E-Commerce application.

TECH STACK
- Backend: Laravel REST API
- Frontend: Vue 3 + TypeScript + Vite
- Database: MySQL 8
- Cache: Redis
- Queue: Redis
- Web Server: Nginx
- Environment: Docker / Docker Compose
- HTTP Client: Axios
- Authentication: Laravel Sanctum

IMPORTANT RULES

1. DO NOT optimize blindly.
2. First measure the current performance.
3. Identify the actual bottleneck before changing code.
4. Do not rewrite the whole application.
5. Do not remove existing functionality.
6. Preserve existing business logic.
7. Preserve the current UI/UX.
8. Do not introduce unnecessary dependencies.
9. Do not add caching everywhere without considering cache invalidation.
10. Do not sacrifice data correctness for performance.
11. Never cache private user data incorrectly.
12. Never trust frontend-calculated prices, inventory, or order totals.
13. Database correctness has higher priority than performance.
14. Test every optimization after implementing it.
15. Compare BEFORE vs AFTER performance.
16. Document every important optimization.

==================================================
1. BASELINE PERFORMANCE
==================================================

First measure the current application.

Check:

- API response time
- Database query time
- Number of database queries
- Memory usage
- CPU usage
- Redis performance
- Laravel request time
- Nginx response time
- Frontend initial load
- JavaScript bundle size
- CSS size
- Image size
- API payload size
- Time to First Byte
- Largest Contentful Paint
- Cumulative Layout Shift
- First Contentful Paint

Do NOT make changes before collecting a baseline.

Create a baseline report:

| Area | Current | Target | Status |
|------|---------|--------|--------|
| API response | ... | ... | ... |
| DB queries | ... | ... | ... |
| Frontend bundle | ... | ... | ... |
| Page load | ... | ... | ... |
| Memory | ... | ... | ... |

==================================================
2. DOCKER PERFORMANCE
==================================================

Inspect:

docker-compose.yml
Dockerfiles
Volumes
Networks
Container resources

Check:

- unnecessary containers
- unnecessary volume mounts
- slow bind mounts
- excessive logging
- incorrect restart policies
- container resource usage
- unnecessary rebuilds
- inefficient Docker images

Optimize:

- Dockerfile layer caching
- dependency installation
- production image size
- PHP extensions
- Node build process
- Nginx configuration
- container startup time

Do not remove required development functionality.

==================================================
3. LARAVEL PERFORMANCE
==================================================

Inspect:

- Controllers
- Services
- Models
- Repositories if used
- Resources
- Requests
- Middleware
- Jobs
- Events
- Notifications

Look for:

- N+1 queries
- unnecessary queries
- duplicate queries
- queries inside loops
- unnecessary model loading
- unnecessary relationships
- large responses
- repeated calculations
- unnecessary middleware
- expensive operations during HTTP requests

Optimize using appropriate Laravel techniques:

- eager loading
- lazy eager loading when appropriate
- query builder where appropriate
- select only required columns
- pagination
- chunking
- cursor pagination
- caching
- queued jobs
- database transactions

Example:

BAD:

Product::with('category', 'brand', 'images', 'variants')
    ->get();

if the API only needs category name.

Prefer selecting only required relationships/columns.

==================================================
4. N+1 QUERY DETECTION
==================================================

Systematically inspect all endpoints.

Especially:

- Products
- Categories
- Brands
- Orders
- Order items
- Customers
- Reviews
- Inventory
- Dashboard
- Reports

Detect patterns such as:

foreach ($products as $product) {
    $product->category;
}

Fix using appropriate eager loading.

Measure:

BEFORE:
- Query count
- Response time

AFTER:
- Query count
- Response time

Do not add eager loading unnecessarily if the relationship is not needed.

==================================================
5. DATABASE PERFORMANCE
==================================================

Analyze the MySQL database.

Check:

- indexes
- foreign keys
- unique indexes
- composite indexes
- slow queries
- full table scans
- sorting
- filtering
- JOIN performance
- GROUP BY
- ORDER BY

Inspect important tables:

users
addresses
categories
brands
products
product_images
product_variants
attributes
attribute_values
variant_attribute_values
inventories
inventory_transactions
carts
cart_items
wishlists
wishlist_items
orders
order_items
order_status_histories
payments
payment_transactions
shipping_methods
shipments
coupons
coupon_usages
reviews
review_images
notifications

Use EXPLAIN / EXPLAIN ANALYZE where appropriate.

Find queries that scan unnecessary rows.

Add indexes only when justified by actual query patterns.

Pay special attention to:

products:
- slug
- SKU
- category_id
- brand_id
- status

orders:
- user_id
- status
- created_at

order_items:
- order_id
- product_id
- variant_id

inventory:
- product_id
- variant_id

reviews:
- product_id
- user_id

notifications:
- user_id
- read_at
- created_at

Do not add excessive indexes because indexes also increase write cost.

==================================================
6. DATABASE PAGINATION
==================================================

Find endpoints returning large datasets.

Replace:

Model::all()

when the dataset can grow significantly.

Use:

paginate()

or:

cursorPaginate()

where appropriate.

Especially for:

- products
- orders
- customers
- reviews
- notifications
- inventory transactions
- reports

Never return thousands of records unnecessarily.

==================================================
7. REDIS OPTIMIZATION
==================================================

Inspect Redis usage.

Use Redis for appropriate workloads:

- caching
- sessions if configured
- queues
- temporary data
- rate limiting

Potential cache targets:

- categories
- brands
- product filters
- popular products
- shipping methods
- public configuration

Be careful with:

- user-specific data
- carts
- inventory
- order totals
- payment state

Do not cache data that can become incorrect without a proper invalidation strategy.

Implement:

- cache TTL
- cache keys
- cache invalidation
- cache versioning where useful

Example:

products:category:{id}:page:{page}

Do not create one enormous cache object containing all products.

==================================================
8. QUEUE / BACKGROUND JOBS
==================================================

Move expensive operations out of normal HTTP requests when appropriate.

Potential jobs:

- sending emails
- sending Telegram notifications
- generating reports
- processing large exports
- image processing
- notification delivery
- large data imports

Do NOT queue operations that require an immediate response unless the UI is designed to handle asynchronous processing.

Verify:

- failed jobs
- retry policy
- timeout
- backoff
- duplicate jobs
- idempotency

==================================================
9. API PERFORMANCE
==================================================

Analyze every major API endpoint.

Check:

- response time
- query count
- payload size
- serialization cost
- pagination
- unnecessary fields

Return only required data.

Avoid returning:

- unnecessary relationships
- unused fields
- huge image metadata
- internal fields

Use API Resources consistently.

Example:

Instead of returning the entire Product model:

{
    "id": 1,
    "name": "...",
    "created_at": "...",
    "updated_at": "...",
    "internal_field": "...",
    ...
}

return only fields required by the frontend.

==================================================
10. API CACHING
==================================================

Identify safe GET endpoints for caching.

Potential candidates:

GET /categories
GET /brands
GET /shipping-methods
GET /products
GET /products/{id}

But carefully handle:

- product stock
- price changes
- authentication
- user-specific data

Cache public data only when safe.

Invalidate cache when:

- product updated
- product deleted
- category updated
- brand updated
- relevant inventory/price changes

==================================================
11. FRONTEND PERFORMANCE
==================================================

Inspect Vue 3 application.

Check:

- component rendering
- unnecessary re-renders
- watchers
- computed properties
- API requests
- Pinia stores
- large components
- duplicated requests
- unnecessary reactive state

Use:

- computed()
- shallowRef() where appropriate
- markRaw() where appropriate
- lazy loading
- dynamic imports
- route-level code splitting

Do not use optimization techniques without understanding their effect.

==================================================
12. VUE ROUTE LAZY LOADING
==================================================

Check whether large pages are loaded only when needed.

Example:

const ProductPage = () =>
    import('@/pages/products/ProductPage.vue')

Apply lazy loading to appropriate routes:

- Dashboard
- Products
- Orders
- Reports
- Settings
- Customers

Do not lazy-load tiny components unnecessarily.

==================================================
13. FRONTEND API REQUEST OPTIMIZATION
==================================================

Find:

- duplicate API calls
- API calls triggered multiple times
- unnecessary requests
- requests triggered on every keystroke
- requests that can be combined

Implement where appropriate:

- debounce search
- request cancellation
- pagination
- caching
- request deduplication

Example:

Product search should not send an API request for every individual keystroke.

Use debounce.

==================================================
14. IMAGE PERFORMANCE
==================================================

Inspect all product/category/brand/review images.

Optimize:

- image dimensions
- image compression
- WebP/AVIF where appropriate
- thumbnails
- lazy loading
- responsive images

Do not load a 3000px image when a 300px thumbnail is displayed.

Use:

loading="lazy"

for appropriate images.

Do not lazy-load important above-the-fold images unnecessarily.

==================================================
15. FRONTEND BUNDLE
==================================================

Run:

npm run build

Inspect:

- JavaScript bundle size
- CSS size
- duplicated dependencies
- large libraries
- unused dependencies

Find unnecessarily large dependencies.

Use tree-shaking and dynamic imports where appropriate.

Do not replace libraries just for tiny theoretical improvements.

==================================================
16. ADMIN DASHBOARD PERFORMANCE
==================================================

Optimize:

Dashboard
Products
Categories
Brands
Inventory
Orders
Payments
Shipping
Customers
Coupons
Reviews
Notifications
Reports
Settings

Especially dashboard statistics.

Avoid making many independent API requests such as:

GET /products/count
GET /orders/count
GET /customers/count
GET /revenue
GET /inventory
GET /reviews

if they can safely be combined into an efficient dashboard endpoint.

But do not create a huge endpoint that returns unnecessary data.

For reports:

- use aggregation queries
- pagination
- caching where appropriate
- background jobs for expensive exports

==================================================
17. PRODUCT LIST PERFORMANCE
==================================================

Product listing is a critical performance area.

Optimize:

- search
- category filtering
- brand filtering
- price filtering
- sorting
- pagination

Do not load:

- all products
- all images
- all variants
- all reviews

for every product listing request.

Load only what the page requires.

==================================================
18. SEARCH PERFORMANCE
==================================================

Inspect product search.

Check:

- LIKE queries
- indexes
- filtering
- sorting
- pagination

Do not perform expensive queries such as:

WHERE name LIKE '%keyword%'

on huge datasets without understanding the performance implications.

If the dataset becomes large, evaluate an appropriate search solution.

Do not introduce Elasticsearch/Meilisearch/etc. unless the existing database approach is actually insufficient.

==================================================
19. CART PERFORMANCE
==================================================

Optimize cart operations.

Check:

- cart loading
- cart item loading
- product/variant loading
- price calculation
- stock checking

Avoid repeated database queries.

But always validate:

- current price
- current stock
- product availability

on the backend.

==================================================
20. CHECKOUT PERFORMANCE
==================================================

Checkout must remain CORRECT before being fast.

Optimize:

Cart
→ Validation
→ Inventory
→ Coupon
→ Shipping
→ Payment
→ Order

Use database transactions.

Avoid unnecessary queries.

Prevent:

- duplicate checkout
- duplicate orders
- overselling
- inconsistent inventory

Do not cache transactional state incorrectly.

==================================================
21. ORDER PERFORMANCE
==================================================

Optimize:

- order listing
- order details
- order history
- admin order management

Use:

- pagination
- eager loading
- selected columns
- indexes

Do not load thousands of orders at once.

==================================================
22. REPORT PERFORMANCE
==================================================

Reports can be expensive.

Check:

- sales reports
- revenue
- products sold
- inventory reports
- customer reports

Use:

- SQL aggregation
- indexes
- caching
- queued report generation
- precomputed summaries where justified

Do not calculate large reports by loading every row into PHP.

Prefer database aggregation.

==================================================
23. HTTP / NGINX PERFORMANCE
==================================================

Inspect Nginx.

Check:

- gzip/brotli where supported
- static file caching
- cache headers
- keep-alive
- compression
- unnecessary proxy overhead

Configure appropriate cache headers for static assets.

Never cache sensitive authenticated API responses publicly.

==================================================
24. LARAVEL PRODUCTION OPTIMIZATION
==================================================

For production, verify:

APP_ENV=production
APP_DEBUG=false

Use Laravel optimization commands where appropriate:

php artisan config:cache
php artisan route:cache
php artisan view:cache

Do not run production optimization blindly in local development if it interferes with development configuration.

==================================================
25. PHP PERFORMANCE
==================================================

Check:

- PHP OPcache
- memory limits
- execution time
- unnecessary object creation
- expensive loops

Enable and configure OPcache appropriately for production.

==================================================
26. MEMORY LEAKS
==================================================

Check:

- long-running queue workers
- large exports
- large collections
- large API responses
- frontend event listeners
- timers
- watchers

Avoid:

Model::all()

for huge datasets.

Use:

chunk()
lazy()
cursor()

where appropriate.

==================================================
27. CONCURRENCY PERFORMANCE
==================================================

Test multiple users simultaneously.

Focus on:

- product purchases
- inventory
- checkout
- coupons
- payments

Test:

10 concurrent users
50 concurrent users
100 concurrent users

if the local environment can reasonably support it.

Do not claim a specific capacity unless it was actually tested.

Identify:

- race conditions
- database locks
- slow queries
- connection exhaustion
- queue bottlenecks

==================================================
28. LOAD TESTING
==================================================

If load-testing tools are available, perform controlled tests against the local/staging environment.

Measure:

- requests per second
- average latency
- p95 latency
- p99 latency
- error rate
- CPU
- memory
- database load

Test important endpoints:

- product listing
- product detail
- login
- cart
- checkout
- order listing
- admin dashboard

Do not perform load testing against production without explicit authorization.

==================================================
29. FRONTEND USER EXPERIENCE
==================================================

Improve perceived performance.

Use:

- skeleton loading
- proper loading indicators
- optimistic UI only where safe
- pagination
- lazy loading
- cached data
- smaller payloads

Do not hide slow operations with fake loading animations.

==================================================
30. PERFORMANCE REGRESSION TEST
==================================================

After every optimization:

1. Run the relevant tests.
2. Verify functionality.
3. Measure performance again.
4. Compare BEFORE vs AFTER.
5. Check for regressions.

Create:

PERFORMANCE BEFORE

and:

PERFORMANCE AFTER

Example:

Product API:

Before:
- 42 queries
- 850ms

After:
- 8 queries
- 190ms

Only report actual measured numbers.

==================================================
31. CODE QUALITY
==================================================

While optimizing:

- remove unnecessary queries
- remove duplicate API calls
- remove dead code if clearly safe
- simplify expensive logic
- avoid premature optimization
- maintain readable code
- maintain Laravel conventions
- maintain Vue conventions
- maintain TypeScript type safety

Do not sacrifice maintainability for micro-optimizations.

==================================================
32. FINAL PERFORMANCE REPORT
==================================================

Provide:

1. Performance baseline
2. Bottlenecks found
3. Optimizations performed
4. Files changed
5. Database indexes added
6. Queries optimized
7. API optimizations
8. Redis optimizations
9. Queue optimizations
10. Vue optimizations
11. Image optimizations
12. Docker optimizations
13. Nginx optimizations
14. Security considerations
15. Before/after measurements
16. Remaining bottlenecks
17. Recommended future improvements

Use this table:

| Area | Before | After | Improvement | Verified |
|------|--------|-------|-------------|----------|
| Product API | ... | ... | ... | YES |
| Product queries | ... | ... | ... | YES |
| Dashboard | ... | ... | ... | YES |
| Frontend bundle | ... | ... | ... | YES |
| Page load | ... | ... | ... | YES |

==================================================
FINAL REQUIREMENT
==================================================

Do not simply make the application "feel faster."

MEASURE → IDENTIFY → OPTIMIZE → TEST → MEASURE AGAIN.

Do not claim performance improvements without measurements.

Prioritize:

1. Database bottlenecks
2. N+1 queries
3. Large API responses
4. Missing pagination
5. Slow frontend requests
6. Large assets
7. Expensive dashboard/report queries
8. Queue/background processing
9. Caching
10. Docker/Nginx configuration

Keep correctness, security, and data consistency as the highest priority.


Recommended performance architecture

For your E-Commerce project, the target flow should be:

                    ┌───────────────┐
                    │   Vue 3 SPA   │
                    └───────┬───────┘
                            │
                       Axios / API
                            │
                    ┌───────▼───────┐
                    │     Nginx     │
                    └───────┬───────┘
                            │
                    ┌───────▼───────┐
                    │ Laravel API   │
                    └───┬───────┬───┘
                        │       │
              ┌─────────┘       └─────────┐
              ▼                           ▼
        ┌──────────┐                ┌──────────┐
        │  Redis   │                │  MySQL   │
        │ Cache    │                │ Database │
        │ Queue    │                └──────────┘
        └──────────┘
              │
              ▼
        Background Jobs

The most important optimization areas for your particular E-Commerce system are MySQL indexes + N+1 query elimination + pagination + Redis caching + queueing heavy jobs + Vue lazy loading + image optimization. Don't let an agent add caching everywhere; incorrect caching around inventory, cart totals, checkout, orders, and payments can create serious data-consistency bugs.