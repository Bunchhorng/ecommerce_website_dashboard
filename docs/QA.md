You are a Senior QA Engineer and Software Tester.

Perform a COMPLETE QA TEST of this E-Commerce project.

Tech stack:
- Backend: Laravel REST API
- Frontend: Vue 3 + TypeScript + Vite
- Database: MySQL 8
- Cache/Queue: Redis
- Web Server: Nginx
- Environment: Docker / Docker Compose
- API communication: Axios
- Authentication: Laravel Sanctum

IMPORTANT:
- Do NOT modify code immediately.
- First inspect and understand the entire project.
- Test the existing implementation before making changes.
- Do not assume that something works just because the code exists.
- Identify actual bugs, broken flows, missing validation, security problems, UI problems, and edge cases.
- Do not remove existing functionality.
- Do not rewrite the project unnecessarily.
- Preserve the current architecture unless a change is required to fix a real problem.

==================================================
1. PROJECT STRUCTURE QA
==================================================

Inspect:

- backend/
- frontend/
- docker/
- docker-compose.yml
- environment configuration
- Laravel configuration
- Vue configuration
- Nginx configuration
- Docker volumes
- Docker networks

Verify:

- Laravel starts correctly.
- Vue starts correctly.
- All Docker containers start correctly.
- Services can communicate with each other.
- MySQL connection works.
- Redis connection works.
- Nginx routing works.
- Frontend can communicate with Laravel API.
- No broken environment variables.
- No hardcoded production secrets.

==================================================
2. DOCKER QA
==================================================

Run and verify:

docker compose config
docker compose ps
docker compose ps -a
docker compose logs
docker compose up -d
docker compose down

Check:

- frontend container
- backend/app container
- nginx container
- mysql container
- redis container
- phpMyAdmin container

Verify:

- Containers do not unexpectedly restart.
- No crash loops.
- No missing package.json.
- No missing composer.json.
- No permission errors.
- No port conflicts.
- Volumes work correctly.
- Database data persists after container restart.
- Frontend node_modules works correctly.
- Laravel storage works correctly.

==================================================
3. BACKEND QA
==================================================

Inspect all Laravel code.

Check:

- Routes
- Controllers
- Models
- Migrations
- Seeders
- Form Requests
- Services
- Resources
- Policies
- Middleware
- Authentication
- Authorization
- Jobs
- Events
- Notifications
- Exceptions
- API responses

Verify:

- REST API follows consistent conventions.
- HTTP status codes are correct.
- Validation is implemented.
- Validation errors are returned correctly.
- Database transactions are used where necessary.
- Exceptions are handled correctly.
- No duplicated business logic.
- No unnecessary queries.
- No N+1 query problems.
- No mass-assignment vulnerabilities.
- No insecure direct object references.
- No unauthorized access to admin APIs.

==================================================
4. DATABASE QA
==================================================

Review all migrations and relationships.

Main entities:

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

Verify:

- Primary keys
- Foreign keys
- Unique constraints
- Nullable fields
- Default values
- Indexes
- Cascade behavior
- Soft deletes where appropriate
- Data types
- Decimal precision for prices
- Quantity validation
- Stock consistency

Test:

- Duplicate records
- Invalid foreign keys
- Deleted parent records
- Negative prices
- Negative quantities
- Invalid stock
- Invalid order totals
- Invalid coupon values
- Invalid payment amounts

==================================================
5. AUTHENTICATION QA
==================================================

Test:

- Register
- Login
- Logout
- Get current user
- Token/session handling
- Password validation
- Password hashing
- Invalid credentials
- Expired authentication
- Unauthorized requests
- Multiple users
- Admin authentication
- Customer authentication

Verify:

- Customers cannot access admin endpoints.
- Admins can access permitted admin endpoints.
- Users cannot access another user's private data.
- Passwords are never returned in API responses.

Test:

- Empty email
- Invalid email
- Wrong password
- Empty password
- Very long input
- Duplicate email
- SQL injection payloads
- XSS payloads

==================================================
6. PRODUCT QA
==================================================

Test:

- Create product
- View products
- View product details
- Update product
- Delete product
- Search product
- Filter products
- Sort products
- Pagination
- Product images
- Product variants
- Attributes
- Attribute values
- Brand
- Category

Test edge cases:

- Empty product name
- Duplicate SKU
- Duplicate slug
- Negative price
- Zero price
- Negative stock
- Extremely large quantity
- Missing category
- Missing brand
- Invalid image
- Large image
- Unsupported file type
- Multiple images
- Deleted image

Verify product variant pricing and stock are correct.

==================================================
7. CATEGORY QA
==================================================

Test:

- Create category
- Update category
- Delete category
- List categories
- Search categories
- Category slug
- Category image
- Product/category relationship

Test:

- Duplicate category name
- Duplicate slug
- Empty name
- Invalid image
- Deleting category with products
- Parent/child category behavior if implemented

==================================================
8. BRAND QA
==================================================

Test:

- Create brand
- Update brand
- Delete brand
- List brands
- Search brands
- Brand logo

Test:

- Duplicate brand
- Empty brand name
- Invalid logo
- Delete brand with products

==================================================
9. INVENTORY QA
==================================================

This is a critical area.

Test:

- Stock in
- Stock out
- Stock adjustment
- Inventory transaction
- Available stock
- Reserved stock
- Low-stock detection
- Out-of-stock detection

Verify:

available_stock = stock_quantity - reserved_quantity

Test:

- Negative stock
- Overselling
- Concurrent orders
- Multiple users buying the same product
- Cancelled order
- Returned order
- Refunded order

Verify inventory transactions are atomic and consistent.

==================================================
10. CART QA
==================================================

Test:

- Add item
- Remove item
- Update quantity
- Clear cart
- Cart total
- Variant selection
- Stock validation

Test:

- Add unavailable product
- Add out-of-stock product
- Quantity greater than stock
- Quantity = 0
- Negative quantity
- Deleted product
- Deleted variant
- Price changes after adding to cart

Verify cart totals are calculated server-side.

Never trust price or total values sent from the frontend.

==================================================
11. WISHLIST QA
==================================================

Test:

- Add wishlist item
- Remove wishlist item
- View wishlist
- Duplicate wishlist item
- Deleted product
- Unauthorized wishlist access

Verify one user cannot access another user's wishlist.

==================================================
12. CHECKOUT QA
==================================================

Test the complete flow:

Cart
→ Address
→ Shipping
→ Coupon
→ Payment
→ Order
→ Inventory
→ Notification

Verify:

- Order total
- Subtotal
- Discount
- Shipping cost
- Final total

Test:

- Empty cart
- Invalid address
- Invalid shipping method
- Invalid coupon
- Expired coupon
- Used coupon
- Out-of-stock product
- Payment failure
- Duplicate checkout request
- Refresh during checkout

==================================================
13. ORDER QA
==================================================

Test:

- Create order
- View order
- List orders
- Update order status
- Cancel order
- Order history
- Order items
- Order totals

Test status transitions:

Pending
→ Confirmed
→ Processing
→ Shipped
→ Delivered

Also test:

Pending
→ Cancelled

Verify invalid status transitions are rejected.

Verify customers can only see their own orders.

Admins can manage orders according to their permissions.

==================================================
14. PAYMENT QA
==================================================

Test:

- Payment creation
- Payment success
- Payment failure
- Payment pending
- Payment transaction
- Refund
- Duplicate payment

Verify:

- Payment amount comes from the server.
- Client cannot manipulate payment amount.
- Payment status is consistent with order status.
- Duplicate payment requests do not create duplicate charges/orders.
- Sensitive payment information is not stored unnecessarily.

Use test/sandbox payment environments where applicable.

==================================================
15. SHIPPING QA
==================================================

Test:

- Shipping methods
- Shipping price
- Shipment creation
- Tracking number
- Shipment status
- Delivery status

Test:

- Disabled shipping method
- Invalid shipping method
- Missing address
- Invalid shipping cost
- Duplicate tracking number

==================================================
16. COUPON QA
==================================================

Test:

- Create coupon
- Update coupon
- Delete coupon
- Apply coupon
- Remove coupon
- Coupon expiration
- Usage limit
- Per-user usage limit
- Minimum order amount
- Percentage discount
- Fixed discount

Test:

- Expired coupon
- Disabled coupon
- Invalid coupon
- Coupon used too many times
- Coupon used by unauthorized user
- Discount greater than order total
- Negative discount
- 100% discount
- Duplicate coupon code

==================================================
17. REVIEW QA
==================================================

Test:

- Create review
- Update review
- Delete review
- Rating
- Comment
- Review images
- Review moderation

Verify:

- Only customers who purchased the product can review it, if this rule is implemented.
- User cannot create duplicate reviews when prohibited.
- Rating must be within valid range.
- Invalid image uploads are rejected.
- XSS is prevented in comments.

==================================================
18. NOTIFICATION QA
==================================================

Test notifications for:

- New order
- Payment success
- Payment failure
- Order shipped
- Order delivered
- Low stock
- New review
- Coupon notifications

Verify:

- Correct user receives notification.
- User cannot read another user's private notification.
- Read/unread status works.
- Mark as read works.
- Delete notification works if implemented.

==================================================
19. ADMIN DASHBOARD QA
==================================================

Test:

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

Verify:

- Navigation works.
- Permissions work.
- Tables work.
- Search works.
- Filtering works.
- Sorting works.
- Pagination works.
- Create forms work.
- Edit forms work.
- Delete confirmation works.
- Loading states work.
- Empty states work.
- Error states work.
- Success notifications work.

==================================================
20. CUSTOMER WEBSITE QA
==================================================

Test:

- Home page
- Product listing
- Product details
- Search
- Category
- Brand
- Product filtering
- Product sorting
- Cart
- Wishlist
- Checkout
- Orders
- Profile
- Addresses
- Reviews
- Notifications

Check:

- Responsive design
- Mobile
- Tablet
- Desktop
- Navigation
- Buttons
- Forms
- Modals
- Tables
- Cards
- Images
- Loading states
- Error states
- Empty states

==================================================
21. FRONTEND QA
==================================================

Check Vue code for:

- Component errors
- Console errors
- TypeScript errors
- Broken imports
- Broken routes
- Incorrect props
- Incorrect emits
- Reactive state problems
- Pinia store problems
- API errors
- Race conditions
- Memory leaks
- Unnecessary API requests

Run:

npm run build

The production build must complete without errors.

Also check:

npm run dev

==================================================
22. API QA
==================================================

Test every API endpoint.

For each endpoint verify:

- HTTP method
- URL
- Authentication
- Authorization
- Request validation
- Response status
- Response structure
- Error response
- Pagination
- Filtering
- Sorting

Test:

GET
POST
PUT
PATCH
DELETE

with:

- Valid input
- Invalid input
- Empty input
- Missing fields
- Extra fields
- Unauthorized requests
- Forbidden requests
- Non-existent IDs

==================================================
23. SECURITY QA
==================================================

Perform a security review.

Check for:

- SQL Injection
- XSS
- CSRF
- IDOR
- Mass Assignment
- Broken Access Control
- Authentication bypass
- Authorization bypass
- File upload vulnerabilities
- Path traversal
- Command injection
- Sensitive information exposure
- Debug mode exposure
- Secrets in source code
- Weak validation
- Rate limiting
- Brute-force protection
- CORS configuration
- Security headers

Do not perform destructive attacks against production systems.

Only test the local/development environment.

==================================================
24. PERFORMANCE QA
==================================================

Check:

- API response time
- Database query count
- N+1 queries
- Large product lists
- Pagination
- Image loading
- Dashboard reports
- Search performance
- Redis caching
- Queue processing

Test with:

- 100 products
- 1,000 products
- 10,000 products if practical

Identify slow queries and unnecessary requests.

==================================================
25. CONCURRENCY QA
==================================================

Test multiple users performing actions simultaneously.

Especially:

- Multiple users buying the same product
- Multiple checkout requests
- Multiple coupon usages
- Multiple inventory updates
- Multiple payment callbacks

Verify:

- No overselling
- No duplicate orders
- No duplicate payment transactions
- No corrupted inventory
- No inconsistent order totals

Use database transactions and row locking where appropriate.

==================================================
26. FILE UPLOAD QA
==================================================

Test:

- Product images
- Brand logos
- Category images
- Review images
- User images

Test:

- JPG
- JPEG
- PNG
- WEBP
- Invalid extensions
- Very large files
- Empty files
- Malicious filenames
- Duplicate filenames

Verify uploaded files cannot execute server-side code.

==================================================
27. ERROR HANDLING QA
==================================================

Check:

- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 409 Conflict
- 422 Validation Error
- 429 Too Many Requests
- 500 Server Error

API errors should return consistent JSON responses.

Do not expose:

- Stack traces
- Database credentials
- SQL queries
- Internal paths
- Secrets

in production responses.

==================================================
28. TEST DATA QA
==================================================

Create realistic test data.

Examples:

- Admin users
- Customers
- Categories
- Brands
- Products
- Product variants
- Attributes
- Inventory
- Orders
- Payments
- Coupons
- Reviews

Test both small and large datasets.

==================================================
29. AUTOMATED TESTING
==================================================

Inspect existing tests.

Backend:

- Unit tests
- Feature tests
- API tests

Frontend:

- Component tests
- Store tests
- Utility tests

Add tests where important functionality has no coverage.

Run Laravel tests:

php artisan test

Run frontend tests if configured.

Run:

npm run build

==================================================
30. FINAL QA REPORT
==================================================

Do NOT simply say "everything is good."

Create a detailed QA report with:

1. Overall project status
2. Environment status
3. Docker status
4. Backend status
5. Frontend status
6. Database status
7. API status
8. Authentication status
9. Authorization status
10. Product status
11. Inventory status
12. Cart status
13. Wishlist status
14. Checkout status
15. Order status
16. Payment status
17. Shipping status
18. Coupon status
19. Review status
20. Notification status
21. Admin dashboard status
22. Customer website status
23. Security findings
24. Performance findings
25. Concurrency findings
26. Automated test results

For every problem report:

- Bug ID
- Severity
- Priority
- Module
- File
- Line number
- Steps to reproduce
- Expected result
- Actual result
- Root cause
- Recommended fix

Use severity:

CRITICAL
HIGH
MEDIUM
LOW

Do not hide bugs.

==================================================
IMPORTANT QA RULES
==================================================

1. Inspect before modifying.
2. Test real behavior, not only source code.
3. Do not assume functionality works.
4. Do not delete working features.
5. Do not rewrite the architecture unnecessarily.
6. Preserve existing UI/UX unless a problem is found.
7. Test both positive and negative cases.
8. Test authorization for every protected resource.
9. Never trust frontend-calculated prices, totals, stock, or permissions.
10. Verify important business logic on the backend.
11. Test database transactions for financial and inventory operations.
12. Check logs for hidden errors.
13. Check browser console for frontend errors.
14. Check network requests in the frontend.
15. Check API responses.
16. Check database state after important operations.
17. Test mobile and desktop layouts.
18. Test empty/loading/error states.
19. Do not use production credentials.
20. Only perform security testing against the authorized local/development environment.

==================================================
FINAL OUTPUT
==================================================

At the end, provide:

A. QA SUMMARY

B. CRITICAL BUGS

C. HIGH PRIORITY BUGS

D. MEDIUM PRIORITY BUGS

E. LOW PRIORITY BUGS

F. SECURITY FINDINGS

G. PERFORMANCE FINDINGS

H. TESTS PASSED

I. TESTS FAILED

J. RECOMMENDED FIX ORDER

K. FILES THAT NEED CHANGES

L. FINAL QA CHECKLIST

Do not claim a test passed unless you actually executed or verified it.



Recommended QA workflow

For your project, I would run the QA in this order:

Docker
  ↓
Laravel
  ↓
MySQL / Redis
  ↓
API
  ↓
Authentication
  ↓
Products
  ↓
Inventory
  ↓
Cart / Wishlist
  ↓
Checkout
  ↓
Orders
  ↓
Payments
  ↓
Shipping
  ↓
Coupons
  ↓
Reviews
  ↓
Notifications
  ↓
Admin Dashboard
  ↓
Customer Website
  ↓
Security
  ↓
Performance
  ↓
Final QA Report