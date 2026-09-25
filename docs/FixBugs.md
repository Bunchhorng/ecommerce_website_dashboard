You are a Senior Full-Stack Engineer, QA Engineer, and Debugging Specialist.

Your task is to FULLY INSPECT, FIND, TEST, and FIX BUGS in this E-Commerce project.

TECH STACK
- Backend: Laravel REST API
- Frontend: Vue 3 + TypeScript + Vite
- Database: MySQL 8
- Cache/Queue: Redis
- Web Server: Nginx
- Environment: Docker / Docker Compose
- HTTP Client: Axios
- Authentication: Laravel Sanctum

PROJECT STRUCTURE

backend/
frontend/
docker/
docker-compose.yml

==================================================
IMPORTANT RULES
==================================================

1. First inspect the entire project.
2. Do NOT immediately modify files.
3. Understand the existing architecture before making changes.
4. Find the root cause of each bug.
5. Fix the root cause, not just the visible symptom.
6. Do NOT rewrite working code unnecessarily.
7. Do NOT remove existing features.
8. Do NOT change the database architecture unless necessary.
9. Preserve the existing UI/UX unless a bug requires a UI change.
10. Do not introduce unnecessary dependencies.
11. Do not create duplicate components, services, controllers, or utilities.
12. Do not leave TODO placeholders for bugs that you can fix.
13. After every important fix, test the affected functionality.
14. Run the application after changes.
15. Check both backend and frontend.
16. Check Docker after changes.
17. Check browser console errors.
18. Check API/network errors.
19. Check Laravel logs.
20. Check database errors.
21. Never claim a bug is fixed unless you actually verify it.

==================================================
PHASE 1 — PROJECT INSPECTION
==================================================

Inspect:

backend/
frontend/
docker/
docker-compose.yml

Read and understand:

Backend:
- routes
- controllers
- models
- migrations
- seeders
- requests
- resources
- services
- middleware
- policies
- jobs
- events
- notifications
- config
- .env configuration

Frontend:
- components
- pages/views
- layouts
- router
- Pinia stores
- services
- Axios configuration
- composables
- TypeScript types
- utilities
- forms
- tables
- modals

Docker:
- Dockerfile
- docker-compose.yml
- Nginx configuration
- volumes
- networks
- environment variables

==================================================
PHASE 2 — ENVIRONMENT / DOCKER BUGS
==================================================

Run:

docker compose config
docker compose ps -a
docker compose logs

Check every service:

- app
- frontend
- nginx
- mysql
- redis
- phpmyadmin

Find and fix:

- containers not starting
- containers crashing
- restart loops
- missing files
- missing package.json
- missing composer.json
- incorrect working directories
- incorrect volume mappings
- incorrect ports
- incorrect environment variables
- incorrect Docker networking
- PHP errors
- Node/npm errors
- Composer errors
- permission errors
- Nginx configuration errors
- MySQL connection errors
- Redis connection errors

After fixing:

docker compose up -d --build

Then verify:

docker compose ps

All required services should be healthy/running.

==================================================
PHASE 3 — BACKEND BUG CHECK
==================================================

Inspect all Laravel code.

Check for:

- syntax errors
- undefined variables
- undefined methods
- incorrect imports
- incorrect namespaces
- incorrect route names
- incorrect model relationships
- incorrect validation
- incorrect middleware
- incorrect authorization
- incorrect query logic
- incorrect response format
- incorrect status codes
- missing error handling
- missing transactions
- duplicated logic
- N+1 queries
- incorrect pagination
- incorrect filtering
- incorrect sorting

Run:

php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan test

Fix all actual errors found.

==================================================
PHASE 4 — DATABASE BUG CHECK
==================================================

Inspect every migration and model relationship.

Entities include:

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

Check:

- foreign keys
- relationships
- indexes
- unique constraints
- nullable fields
- default values
- decimal precision
- timestamps
- soft deletes
- cascade behavior

Find:

- broken relationships
- wrong foreign keys
- incorrect table names
- incorrect column names
- duplicate data
- orphan records
- inconsistent data
- incorrect constraints

Do not change existing production data unless explicitly instructed.

==================================================
PHASE 5 — AUTHENTICATION BUGS
==================================================

Test:

- register
- login
- logout
- current user
- authentication middleware
- Sanctum authentication
- password validation
- invalid credentials
- unauthorized requests
- expired/invalid authentication

Fix:

- login failures
- token/session problems
- incorrect middleware
- incorrect user retrieval
- authentication state problems

Verify:

Customer cannot access admin functionality.

Admin cannot access another user's private resources unless authorized.

==================================================
PHASE 6 — AUTHORIZATION BUGS
==================================================

Test every protected endpoint.

Verify:

ADMIN
- can access admin resources according to permissions

CUSTOMER
- can access only their own resources

Test:

- another user's order
- another user's address
- another user's cart
- another user's wishlist
- another user's notifications
- another user's reviews

Fix any IDOR or authorization vulnerability.

==================================================
PHASE 7 — PRODUCT BUGS
==================================================

Test:

- create product
- update product
- delete product
- product details
- product listing
- search
- filter
- sort
- pagination
- product images
- variants
- attributes
- brands
- categories

Check:

- duplicate SKU
- duplicate slug
- invalid price
- negative price
- invalid stock
- missing category
- missing brand
- invalid images
- deleted product
- deleted variant

Fix all real bugs.

==================================================
PHASE 8 — CATEGORY BUGS
==================================================

Test:

- create
- update
- delete
- list
- search
- slug
- image
- product relationship

Check:

- duplicate category
- duplicate slug
- invalid category
- deleting category with products

==================================================
PHASE 9 — BRAND BUGS
==================================================

Test:

- create
- update
- delete
- list
- search
- logo

Check:

- duplicate brand
- invalid logo
- deleting brand with products

==================================================
PHASE 10 — INVENTORY BUGS
==================================================

This is a HIGH-PRIORITY area.

Test:

- stock in
- stock out
- stock adjustment
- reservation
- available stock
- low stock
- out of stock
- inventory transactions

Verify:

available_stock =
stock_quantity - reserved_quantity

Test:

- quantity = 0
- negative quantity
- quantity greater than stock
- concurrent purchases
- order cancellation
- refund
- returned order

Fix:

- overselling
- incorrect stock calculation
- duplicate inventory transactions
- race conditions
- incorrect reservation logic

Use database transactions/locking where appropriate.

==================================================
PHASE 11 — CART BUGS
==================================================

Test:

- add product
- add variant
- update quantity
- remove item
- clear cart
- calculate subtotal
- calculate total

Check:

- out-of-stock product
- deleted product
- deleted variant
- quantity greater than stock
- quantity = 0
- negative quantity
- price changed after adding to cart

IMPORTANT:

Never trust prices or totals from the frontend.

Calculate important values on the backend.

==================================================
PHASE 12 — WISHLIST BUGS
==================================================

Test:

- add
- remove
- list
- duplicate item
- deleted product

Verify users cannot access another user's wishlist.

==================================================
PHASE 13 — CHECKOUT BUGS
==================================================

Test complete flow:

Cart
→ Address
→ Shipping
→ Coupon
→ Payment
→ Order
→ Inventory
→ Notification

Check:

- subtotal
- discount
- shipping fee
- tax if implemented
- final total

Test:

- empty cart
- invalid address
- invalid shipping
- invalid coupon
- expired coupon
- out-of-stock product
- payment failure
- duplicate checkout
- double-click checkout
- page refresh during checkout

Fix inconsistent totals and duplicate orders.

==================================================
PHASE 14 — ORDER BUGS
==================================================

Test:

- create order
- list orders
- order details
- update status
- cancel order
- order history

Verify status flow:

Pending
→ Confirmed
→ Processing
→ Shipped
→ Delivered

And:

Pending
→ Cancelled

Prevent invalid transitions.

Check:

- incorrect totals
- missing order items
- incorrect customer
- incorrect stock
- incorrect status history

==================================================
PHASE 15 — PAYMENT BUGS
==================================================

Test:

- pending
- success
- failed
- refund
- duplicate payment
- payment transaction

Verify:

- amount is calculated server-side
- client cannot manipulate amount
- payment belongs to correct order
- duplicate callbacks do not create duplicate records
- payment status is consistent with order status

Do not use real payment credentials during testing.

==================================================
PHASE 16 — SHIPPING BUGS
==================================================

Test:

- shipping methods
- shipping price
- shipment creation
- tracking number
- shipment status

Check:

- invalid shipping method
- disabled shipping method
- invalid address
- duplicate tracking number
- incorrect shipping cost

==================================================
PHASE 17 — COUPON BUGS
==================================================

Test:

- create
- update
- delete
- apply
- remove
- expiration
- usage limit
- per-user usage
- minimum order
- percentage discount
- fixed discount

Check:

- expired coupon
- disabled coupon
- invalid coupon
- duplicate coupon
- negative discount
- discount greater than order total
- excessive usage

==================================================
PHASE 18 — REVIEW BUGS
==================================================

Test:

- create
- update
- delete
- rating
- comment
- images

Check:

- invalid rating
- unauthorized review
- duplicate review
- deleted product
- invalid image
- XSS in review comments

==================================================
PHASE 19 — NOTIFICATION BUGS
==================================================

Test:

- create notification
- list notifications
- read notification
- mark as read
- unread count
- delete notification

Verify notifications belong to the correct user.

==================================================
PHASE 20 — FRONTEND BUGS
==================================================

Inspect all Vue components.

Check:

- runtime errors
- TypeScript errors
- incorrect props
- incorrect emits
- broken imports
- broken routes
- incorrect Pinia state
- incorrect computed values
- incorrect watchers
- incorrect lifecycle hooks
- memory leaks
- duplicate API requests
- race conditions

Run:

npm run build

Fix every build error.

==================================================
PHASE 21 — FRONTEND API BUGS
==================================================

Inspect Axios/API services.

Check:

- base URL
- authentication
- headers
- token handling
- error handling
- loading state
- timeout
- response handling

Check browser Network requests.

Verify:

Frontend request
→ Nginx
→ Laravel
→ Database
→ Laravel response
→ Vue

works correctly.

==================================================
PHASE 22 — UI BUGS
==================================================

Test:

- desktop
- tablet
- mobile

Check:

- broken layout
- overflow
- buttons
- forms
- tables
- modals
- dropdowns
- navigation
- sidebar
- pagination
- images
- loading state
- empty state
- error state
- success messages

Do not redesign the UI.

Only fix actual bugs.

==================================================
PHASE 23 — SECURITY BUGS
==================================================

Check for:

- SQL Injection
- XSS
- CSRF
- IDOR
- broken authorization
- mass assignment
- authentication bypass
- file upload vulnerabilities
- path traversal
- command injection
- sensitive data exposure
- hardcoded secrets
- insecure CORS
- missing rate limits

Only test the authorized local/development environment.

Fix vulnerabilities safely.

==================================================
PHASE 24 — PERFORMANCE BUGS
==================================================

Check:

- N+1 queries
- unnecessary API calls
- slow database queries
- missing indexes
- huge API responses
- missing pagination
- unnecessary frontend rendering
- unnecessary requests
- image loading problems
- Redis usage

Do not optimize prematurely.

Only optimize actual bottlenecks.

==================================================
PHASE 25 — CONCURRENCY BUGS
==================================================

Test simultaneous actions:

1. Two users buy the last product.
2. Two users apply the same limited coupon.
3. Two checkout requests happen at the same time.
4. Payment callback is received twice.
5. Inventory is updated simultaneously.

Verify:

- no overselling
- no duplicate orders
- no duplicate payments
- no incorrect inventory
- no corrupted data

Use database transactions and row locks where appropriate.

==================================================
PHASE 26 — FIXING PROCESS
==================================================

For every bug:

1. Identify the bug.
2. Reproduce it.
3. Find the root cause.
4. Fix the root cause.
5. Run the relevant test.
6. Check for regression.
7. Check related functionality.
8. Record the fix.

Use this format:

BUG-001
Module: Products
Severity: HIGH

Problem:
...

Root Cause:
...

Fix:
...

Files Changed:
...

Test:
...

Result:
PASS

==================================================
PHASE 27 — REGRESSION TEST
==================================================

After fixing bugs, test the entire critical flow again:

Register
→ Login
→ Browse Products
→ Product Details
→ Add to Cart
→ Update Cart
→ Wishlist
→ Checkout
→ Address
→ Shipping
→ Coupon
→ Payment
→ Order
→ Inventory
→ Shipment
→ Review
→ Notification

Also test:

Admin Login
→ Dashboard
→ Products
→ Categories
→ Brands
→ Inventory
→ Orders
→ Payments
→ Shipping
→ Customers
→ Coupons
→ Reviews
→ Notifications
→ Reports
→ Settings

==================================================
PHASE 28 — FINAL VERIFICATION
==================================================

Run:

docker compose config

docker compose ps -a

docker compose logs

php artisan test

php artisan route:list

npm run build

Check browser console.

Check API requests.

Check database state.

Check Redis.

Check important user flows.

==================================================
FINAL REPORT
==================================================

At the end, provide:

1. Total bugs found
2. Total bugs fixed
3. Remaining bugs
4. Critical bugs
5. High bugs
6. Medium bugs
7. Low bugs
8. Security issues
9. Performance issues
10. Files changed
11. Tests executed
12. Tests passed
13. Tests failed
14. Remaining risks

Use this table:

| ID | Module | Severity | Problem | Root Cause | Fix | Test |
|----|--------|----------|---------|------------|-----|------|
| BUG-001 | Product | HIGH | ... | ... | ... | PASS |

IMPORTANT:

Do not say "fixed" without testing the fix.

Do not say "no bugs found" unless you actually inspected and tested the relevant functionality.

If you find a bug, FIX IT.

After fixing, run the appropriate test again.

Do not stop after finding the first bug.

Continue until the project has been systematically checked.



Short version for repeated debugging

Once your project is large, you can use this shorter prompt for individual tasks:

Act as a Senior Full-Stack Debugging Engineer.

Inspect this E-Commerce project and find the actual bugs in the requested module.

Stack:
- Laravel REST API
- Vue 3 + TypeScript + Vite
- MySQL
- Redis
- Nginx
- Docker

Process:

1. Inspect the existing code first.
2. Reproduce the bug.
3. Check frontend console errors.
4. Check browser Network/API errors.
5. Check Laravel logs.
6. Check database state.
7. Find the root cause.
8. Fix the root cause.
9. Do not rewrite unrelated code.
10. Do not remove existing functionality.
11. Keep the existing architecture.
12. Test the fix.
13. Test related functionality for regression.
14. Run the relevant backend/frontend tests.
15. Run `npm run build` for frontend changes.
16. Verify Docker services if the change affects Docker.

For each bug report:

- Bug
- Root cause
- File
- Line
- Fix
- Test performed
- Result

Do not claim success without verification.

After fixing everything, provide a final summary of:
- Bugs found
- Bugs fixed
- Remaining bugs
- Files changed
- Tests passed
- Tests failed
- Remaining risks