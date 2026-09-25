You are a Senior Full-Stack Engineer specializing in Laravel + Vue + MySQL + Redis + Docker.

I am building an E-Commerce application with:

Backend:
- Laravel
- MySQL
- Redis
- REST API
- Docker
- Nginx

Frontend:
- Vue 3
- TypeScript
- Vite
- Vue Router
- Pinia
- Axios
- Bootstrap
- Bootstrap Icons

Your task is to make the ENTIRE ADMIN DASHBOARD fully functional from frontend to backend to database.

IMPORTANT:
Do NOT only create UI pages.
Do NOT use fake/mock/static data.
Every dashboard feature must work with the real Laravel API and MySQL database.

==================================================
1. FIRST: INSPECT THE EXISTING PROJECT
==================================================

Before changing code:

1. Inspect the complete project structure.
2. Inspect:
   - Laravel routes
   - Models
   - Migrations
   - Controllers
   - Services
   - API Resources
   - Requests/validation
   - Policies/permissions
   - Vue pages
   - Vue components
   - Pinia stores
   - Axios configuration
   - Router
   - Docker configuration
   - Nginx configuration
3. Identify what is already implemented.
4. Identify incomplete features.
5. Identify broken features.
6. Identify frontend pages that currently use mock data.
7. Identify API endpoints that are missing.
8. Do not unnecessarily rewrite working code.

Create an implementation plan before making major changes.

==================================================
2. ADMIN AUTHENTICATION
==================================================

Implement a complete admin authentication system.

Features:

- Admin login
- Logout
- Current authenticated admin
- Authentication persistence
- Protected admin routes
- Unauthorized access handling
- Session/token handling
- Automatic authentication check
- Redirect unauthenticated users to login
- Redirect authenticated users away from login
- Handle expired authentication
- Proper 401/403 handling

Admin routes must not be accessible by normal customers.

Example:

/admin/login
/admin/dashboard
/admin/products
/admin/orders
/admin/customers
...

Use Laravel authentication/authorization correctly.

Never trust frontend-only role checking.

Backend must verify admin permissions.

==================================================
3. ADMIN DASHBOARD
==================================================

Create a real dashboard using database data.

Dashboard should show:

- Total customers
- Total products
- Total categories
- Total brands
- Total orders
- Pending orders
- Processing orders
- Completed orders
- Cancelled orders
- Total revenue
- Today's revenue
- This week's revenue
- This month's revenue
- Low-stock products
- Out-of-stock products
- Recent orders
- Recent customers
- Recent reviews
- Recent payments

Charts:

- Sales over time
- Orders over time
- Revenue over time
- Top-selling products
- Sales by category
- Order status distribution
- Payment status distribution

Allow filtering:

- Today
- Yesterday
- Last 7 days
- Last 30 days
- This month
- Last month
- Custom date range

All statistics must come from real database queries.

Do not calculate important statistics incorrectly on the frontend.

==================================================
4. PRODUCTS MANAGEMENT
==================================================

Admin must be able to:

- View products
- Search products
- Filter products
- Sort products
- Create product
- Edit product
- Delete product
- Restore deleted product if soft deletes are used
- View product details
- Upload product images
- Delete product images
- Set primary image
- Manage product variants
- Manage SKU
- Manage price
- Manage sale price
- Manage stock
- Manage category
- Manage brand
- Manage attributes
- Set product status
- Set product visibility

Support:

- Pagination
- Bulk selection
- Bulk delete
- Bulk status update
- Validation
- Duplicate SKU prevention
- Image validation
- Proper error handling

Product CRUD must use Laravel APIs.

==================================================
5. CATEGORIES
==================================================

Implement:

- Category list
- Search
- Create
- Edit
- Delete
- Restore if applicable
- Active/inactive status
- Product count
- Parent/child categories if supported

Prevent deleting a category when business rules do not allow it.

Show useful validation errors.

==================================================
6. BRANDS
==================================================

Implement:

- Brand list
- Search
- Create
- Edit
- Delete
- Logo upload
- Active/inactive status
- Product count

Use real database data.

==================================================
7. INVENTORY MANAGEMENT
==================================================

Implement complete inventory management.

Show:

- Product
- SKU
- Current stock
- Reserved stock
- Available stock
- Low-stock status
- Out-of-stock status
- Stock movement

Admin can:

- Add stock
- Remove stock
- Adjust stock
- View inventory history
- View inventory transactions

Every inventory modification must be recorded.

Example transaction types:

- purchase
- adjustment
- sale
- return
- cancellation
- damaged
- manual adjustment

Inventory updates must be transactional.

Do not allow stock to become negative unless explicitly supported by the business rules.

==================================================
8. ORDERS MANAGEMENT
==================================================

Implement complete order management.

Admin can:

- View orders
- Search orders
- Filter orders
- Sort orders
- View order details
- View customer
- View order items
- View shipping address
- View payment information
- View order history
- Update order status
- Cancel order when allowed
- Process refund when supported
- Print/download invoice

Order statuses should follow the existing project design.

Example:

pending
confirmed
processing
shipped
delivered
cancelled
refunded

Every status change must create an order status history record.

Never allow invalid status transitions.

==================================================
9. PAYMENTS
==================================================

Implement payment management.

Admin can view:

- Payment ID
- Order
- Customer
- Amount
- Payment method
- Payment status
- Transaction ID
- Created date

Support statuses such as:

- pending
- paid
- failed
- refunded
- partially_refunded

Admin should be able to view payment details and transaction history.

Do not expose sensitive payment information.

Never store raw card numbers, CVV, or other prohibited sensitive payment data.

==================================================
10. SHIPPING
==================================================

Implement shipping management.

Admin can:

- Create shipping methods
- Edit shipping methods
- Enable/disable shipping methods
- Set shipping price
- Set shipping rules if supported
- View shipments
- Update shipment status
- Add tracking number
- View shipping history

Shipment statuses should be consistent with the order workflow.

==================================================
11. CUSTOMERS
==================================================

Implement customer management.

Admin can:

- View customers
- Search customers
- Filter customers
- View customer details
- View customer orders
- View customer addresses
- View customer reviews
- View customer activity
- Enable/disable customer account when supported

Display:

- Name
- Email
- Phone
- Account status
- Number of orders
- Total spending
- Registration date
- Last activity

Do not expose passwords or sensitive authentication data.

==================================================
12. COUPONS / DISCOUNTS
==================================================

Implement:

- Coupon list
- Create coupon
- Edit coupon
- Delete coupon
- Enable/disable coupon
- Expiration date
- Usage limit
- Per-user usage limit
- Minimum order amount
- Maximum discount
- Percentage discount
- Fixed amount discount

Validate coupons correctly on the backend.

Prevent:

- Expired coupons
- Overused coupons
- Invalid coupon combinations
- Negative totals
- Unauthorized discounts

Track coupon usage.

==================================================
13. REVIEWS
==================================================

Implement review moderation.

Admin can:

- View reviews
- Search reviews
- Filter reviews
- View review details
- Approve review
- Reject review
- Delete review
- View review images
- View customer/product information

Only approved reviews should appear publicly if that is the business rule.

==================================================
14. NOTIFICATIONS
==================================================

Implement admin notifications.

Examples:

- New order
- Payment received
- Payment failed
- Low stock
- Out of stock
- New customer
- New review
- Refund request

Admin should be able to:

- View notifications
- Mark as read
- Mark all as read
- Delete notifications if supported

Use Redis/queues where appropriate.

==================================================
15. REPORTS
==================================================

Create an Admin Reports section.

Reports:

- Sales report
- Revenue report
- Orders report
- Product sales report
- Category sales report
- Customer report
- Inventory report
- Payment report
- Coupon report

Filters:

- Date range
- Product
- Category
- Brand
- Order status
- Payment status

Support:

- Table view
- Summary statistics
- Charts
- Export CSV
- Export Excel if already supported
- Export PDF if already supported

Large reports should not block the main request.

Use queues for expensive report generation when appropriate.

==================================================
16. ADMIN SETTINGS
==================================================

Create a Settings section.

Depending on the existing database/design, support:

General settings:
- Store name
- Store email
- Phone
- Address
- Currency
- Timezone

Store settings:
- Maintenance mode
- Product settings
- Order settings
- Inventory settings

Notification settings:
- Email
- Admin notifications
- Customer notifications

Security:
- Change password
- Authentication settings
- Admin session/security settings

Do not expose secrets in the frontend.

==================================================
17. ADMIN SIDEBAR
==================================================

Create a clean professional admin sidebar.

Menu:

Dashboard

Catalog
- Products
- Categories
- Brands

Sales
- Orders
- Payments
- Shipping
- Coupons

Customers
- Customers
- Reviews

Inventory
- Inventory
- Inventory Transactions

Reports
- Sales Reports
- Product Reports
- Customer Reports
- Inventory Reports

Notifications

Settings

Logout

Use Bootstrap Icons.

Do not use emoji.

Do not use gradients.

Keep the design professional and clean.

==================================================
18. DATA TABLES
==================================================

All major admin tables should support:

- Search
- Pagination
- Sorting
- Filtering
- Loading state
- Empty state
- Error state
- Refresh
- Row actions
- Bulk actions where appropriate

Do not load thousands of records into the browser unnecessarily.

Use server-side pagination.

Example:

GET /api/admin/products?page=1&per_page=20

==================================================
19. FRONTEND ARCHITECTURE
==================================================

Use Vue properly.

Recommended structure:

frontend/src/

├── assets/
├── components/
│   ├── admin/
│   ├── common/
│   ├── tables/
│   ├── forms/
│   └── charts/
├── layouts/
│   └── AdminLayout.vue
├── pages/
│   └── admin/
│       ├── Dashboard.vue
│       ├── Products.vue
│       ├── Categories.vue
│       ├── Brands.vue
│       ├── Inventory.vue
│       ├── Orders.vue
│       ├── Payments.vue
│       ├── Shipping.vue
│       ├── Customers.vue
│       ├── Coupons.vue
│       ├── Reviews.vue
│       ├── Notifications.vue
│       ├── Reports.vue
│       └── Settings.vue
├── stores/
│   ├── auth.ts
│   ├── products.ts
│   ├── orders.ts
│   ├── customers.ts
│   └── notifications.ts
├── services/
│   ├── api.ts
│   ├── productService.ts
│   ├── orderService.ts
│   └── ...
├── router/
├── types/
└── utils/

Do not put all logic inside one Vue component.

==================================================
20. BACKEND ARCHITECTURE
==================================================

Use clean Laravel architecture.

Recommended:

app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   ├── Requests/
│   │   └── Admin/
│   └── Resources/
├── Models/
├── Services/
│   └── Admin/
├── Policies/
├── Jobs/
├── Events/
└── Notifications/

Use:

- Form Requests
- API Resources
- Policies
- Services
- Transactions
- Jobs
- Events where appropriate

Avoid putting large business logic directly inside controllers.

==================================================
21. API SECURITY
==================================================

Secure every admin endpoint.

Check:

- Authentication
- Authorization
- Admin role
- Permissions
- Request validation
- Mass assignment protection
- SQL injection protection
- XSS protection
- CSRF considerations
- Rate limiting
- File upload validation
- File type validation
- File size validation
- Access control
- IDOR vulnerabilities

Never trust:

- frontend role
- frontend prices
- frontend totals
- frontend stock
- frontend permissions

The backend must validate everything important.

==================================================
22. DATABASE
==================================================

Inspect all migrations and relationships.

Make sure relationships are correct:

User
Address
Category
Brand
Product
ProductImage
ProductVariant
Attribute
AttributeValue
Inventory
InventoryTransaction
Cart
CartItem
Wishlist
WishlistItem
Order
OrderItem
OrderStatusHistory
Payment
PaymentTransaction
ShippingMethod
Shipment
Coupon
CouponUsage
Review
ReviewImage
Notification

Add indexes where actually needed.

Check foreign keys.

Check cascade/restrict behavior.

Avoid duplicate data.

Use database transactions for important operations.

==================================================
23. PERFORMANCE
==================================================

Optimize the admin dashboard.

Check for:

- N+1 queries
- Duplicate queries
- Missing indexes
- Excessive API requests
- Huge API responses
- Unnecessary frontend rendering
- Large images
- Large JavaScript bundles

Use:

- Eager loading
- Pagination
- Query optimization
- Redis caching where safe
- Lazy-loaded Vue routes
- Debounced search
- Request cancellation/deduplication
- Queues for expensive jobs

Do not cache user-specific or transactional data incorrectly.

==================================================
24. ERROR HANDLING
==================================================

Frontend must properly handle:

400
401
403
404
409
422
429
500

Display useful messages.

Do not show raw Laravel exceptions to users.

Laravel logs should contain technical details.

Frontend should have:

- Loading states
- Error states
- Empty states
- Success notifications
- Validation messages

==================================================
25. RESPONSIVE ADMIN UI
==================================================

Admin dashboard must work on:

- Desktop
- Laptop
- Tablet
- Mobile

Sidebar should become responsive on smaller screens.

Tables should remain usable on mobile.

Forms should be responsive.

Do not create horizontal overflow unnecessarily.

==================================================
26. REAL-TIME / REFRESH
==================================================

Where appropriate, support real-time or near-real-time updates for:

- New orders
- Notifications
- Low stock
- Payment status

If WebSockets are not configured, implement a safe polling/refresh strategy instead of adding unnecessary infrastructure.

==================================================
27. DOCKER
==================================================

Make sure the entire admin system works inside Docker.

Verify:

- Laravel container
- Vue container
- MySQL
- Redis
- Nginx
- phpMyAdmin

Check:

docker compose ps

Check logs:

docker compose logs app
docker compose logs frontend
docker compose logs nginx
docker compose logs mysql
docker compose logs redis

Fix Docker-related issues rather than bypassing Docker.

==================================================
28. TESTING
==================================================

Test every admin feature.

Backend tests:

- Authentication
- Authorization
- Product CRUD
- Category CRUD
- Brand CRUD
- Inventory
- Orders
- Payments
- Shipping
- Customers
- Coupons
- Reviews
- Notifications
- Reports

Frontend tests where the existing project supports them.

Also perform manual browser testing.

==================================================
29. END-TO-END ADMIN WORKFLOW
==================================================

Test this complete workflow:

1. Admin login
2. Open dashboard
3. Create category
4. Create brand
5. Create product
6. Upload product image
7. Create variant
8. Add inventory
9. View product
10. Create customer/order
11. Process order
12. Verify inventory decreases
13. Verify payment
14. Update shipment
15. Complete order
16. Verify dashboard statistics
17. Verify reports
18. Verify notifications
19. Review customer information
20. Logout

Verify that data remains consistent across all modules.

==================================================
30. IMPORTANT BUSINESS RULE
==================================================

Do not optimize or simplify the system by breaking business correctness.

Especially protect:

- Product price
- Cart totals
- Order totals
- Inventory
- Payments
- Coupons
- Refunds
- Order status
- Customer permissions

These must be calculated and validated on the backend.

==================================================
31. FINAL AUDIT
==================================================

After implementation:

1. Run Laravel tests.
2. Run frontend build.
3. Check TypeScript errors.
4. Check Laravel errors.
5. Check browser console.
6. Check network requests.
7. Check API responses.
8. Check database queries.
9. Check Docker containers.
10. Check responsive UI.
11. Check authentication.
12. Check authorization.
13. Check security.
14. Check performance.

Fix all discovered issues.

Do not stop after finding the first problem.

==================================================
32. FINAL REPORT
==================================================

At the end provide:

### Admin Dashboard Status

Dashboard:
- Working / Issues

Products:
- Working / Issues

Categories:
- Working / Issues

Brands:
- Working / Issues

Inventory:
- Working / Issues

Orders:
- Working / Issues

Payments:
- Working / Issues

Shipping:
- Working / Issues

Customers:
- Working / Issues

Coupons:
- Working / Issues

Reviews:
- Working / Issues

Notifications:
- Working / Issues

Reports:
- Working / Issues

Settings:
- Working / Issues

### Bugs Fixed
List every important bug fixed.

### APIs Added
List all new/changed API endpoints.

### Database Changes
List migrations/indexes/relationships changed.

### Frontend Changes
List pages/components/stores/services changed.

### Security Improvements
List security issues fixed.

### Performance Improvements
List actual optimizations made.

### Remaining Issues
Clearly list anything that could not be completed.

IMPORTANT:
Do not claim something is working unless you actually tested it.

Do not use mock data to hide missing backend functionality.

The final result must be a REAL, CONNECTED, PRODUCTION-QUALITY ADMIN DASHBOARD where Vue → Laravel API → MySQL/Redis works correctly.