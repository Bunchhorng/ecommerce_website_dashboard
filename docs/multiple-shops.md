You are a Senior Full-Stack Architect and Engineer.

I have an existing E-Commerce project built with:

Backend:
- Laravel
- MySQL
- Redis
- REST API
- Nginx
- Docker

Frontend:
- Vue 3
- TypeScript
- Vite
- Vue Router
- Pinia
- Axios
- Bootstrap
- Bootstrap Icons

I want to upgrade this project from a single-store E-Commerce system into a
MULTI-SHOP / MULTI-VENDOR E-COMMERCE MARKETPLACE.

Think of the architecture as similar to a marketplace where many independent
shops can sell products through one platform.

IMPORTANT:
Do NOT destroy working features.
Do NOT rebuild the entire project unnecessarily.
FIRST inspect the existing codebase, database, APIs, frontend, Docker setup,
authentication, authorization, products, orders, inventory, payments, and
admin dashboard.

Then create a migration/implementation plan before making major changes.

==================================================
1. CORE MULTI-SHOP CONCEPT
==================================================

The platform must support:

Platform
│
├── Super Admin
│
├── Shop A
│   ├── Products
│   ├── Inventory
│   ├── Orders
│   └── Reports
│
├── Shop B
│   ├── Products
│   ├── Inventory
│   ├── Orders
│   └── Reports
│
└── Shop C
    ├── Products
    ├── Inventory
    ├── Orders
    └── Reports

Customers can browse products from all shops.

A customer can purchase products from multiple shops in one checkout.

The backend must keep shop ownership and shop-level data completely separated.

==================================================
2. USER ROLES
==================================================

Implement these roles:

1. Super Admin
2. Shop Owner
3. Shop Staff
4. Customer

Permissions:

SUPER ADMIN:
- Manage all shops
- Manage all users
- Manage all products
- Manage all orders
- Manage all inventory
- Manage payments
- Manage shipping
- Manage coupons
- Manage reviews
- Manage reports
- Manage platform settings

SHOP OWNER:
- Manage own shop
- Manage own products
- Manage own product images
- Manage own variants
- Manage own inventory
- Manage own orders
- View own customers where appropriate
- View own sales
- View own reports
- Manage own staff

SHOP STAFF:
- Access only assigned shop
- Manage products if permitted
- Manage inventory if permitted
- Process orders if permitted
- Cannot access another shop

CUSTOMER:
- Browse all shops
- Browse all public products
- Add products from multiple shops to cart
- Checkout
- View own orders
- Review purchased products

IMPORTANT:

Never rely only on frontend role checking.

Every backend endpoint must verify:

Authentication
+
Authorization
+
Shop ownership/access

==================================================
3. SHOPS TABLE
==================================================

Create a shops table if it does not already exist.

Suggested fields:

shops
- id
- owner_id
- name
- slug
- logo
- banner
- description
- phone
- email
- address
- status
- verified_at
- created_at
- updated_at
- deleted_at

Possible statuses:

pending
active
suspended
rejected
closed

Use SoftDeletes where appropriate.

Shop slug must be unique.

==================================================
4. SHOP USERS / STAFF
==================================================

A shop can have multiple staff members.

Do NOT assume one shop can only have one user.

Implement an appropriate relationship such as:

shop_users

- id
- shop_id
- user_id
- role
- status
- created_at
- updated_at

Example:

Shop A
├── Owner
├── Manager
└── Staff

Shop B
├── Owner
└── Staff

A user should be able to belong to multiple shops if the business rules
support it.

Do not hard-code a single shop per user unless the existing requirements
explicitly require that.

==================================================
5. PRODUCTS
==================================================

Every shop-owned product must belong to a shop.

Add:

products.shop_id

Relationship:

Shop
    hasMany Products

Product
    belongsTo Shop

Example:

Shop A
├── iPhone
├── MacBook
└── AirPods

Shop B
├── Samsung Galaxy
├── Samsung TV
└── Galaxy Buds

==================================================
6. PRODUCT OWNERSHIP SECURITY
==================================================

This is extremely important.

Shop A must NEVER be able to:

- Edit Shop B products
- Delete Shop B products
- Change Shop B inventory
- View Shop B private information
- View Shop B private orders
- Modify Shop B prices
- Modify Shop B coupons

Example:

PUT /api/shop/products/100

Before updating product 100:

Verify:

authenticated user
→ belongs to shop
→ has permission
→ product.shop_id matches authorized shop

If not:

return 403 Forbidden.

Implement Laravel Policies/Gates or another proper authorization mechanism.

Do NOT trust:

shop_id
from the frontend request.

Determine the authorized shop from the authenticated user/session/context.

==================================================
7. SHOP PRODUCT MANAGEMENT
==================================================

Shop owners should be able to:

- Create products
- Edit products
- Delete products
- Upload images
- Delete images
- Set primary image
- Create variants
- Edit variants
- Manage SKU
- Manage price
- Manage sale price
- Manage stock
- Manage categories
- Manage brands
- Publish/unpublish products

Products must belong to the correct shop automatically.

Do not allow the frontend to arbitrarily assign a product to another shop.

==================================================
8. CATEGORIES AND BRANDS
==================================================

Determine whether categories and brands are:

GLOBAL
or
SHOP-SPECIFIC.

Recommended architecture:

Categories:
Global

Brands:
Global by default

Products:
Shop-specific

Example:

Category:
Electronics

Shop A:
iPhone 15

Shop B:
Samsung S24

Both can use the same Electronics category.

If the existing business requirements require shop-specific categories,
implement that instead.

Do not duplicate global categories unnecessarily.

==================================================
9. INVENTORY
==================================================

Inventory must belong to a shop/product.

Recommended relationship:

Shop
  ↓
Product
  ↓
Inventory

Each shop controls only its own inventory.

Implement:

- Current stock
- Reserved stock
- Available stock
- Low-stock threshold
- Stock adjustment
- Stock history
- Inventory transactions

Example:

Shop A:
iPhone stock = 20

Shop B:
iPhone stock = 50

These must remain completely independent.

Stock changes must use database transactions.

Prevent race conditions during concurrent purchases.

==================================================
10. CART
==================================================

Customers can add products from multiple shops.

Example:

Cart:

Shop A
- Product A × 2

Shop B
- Product B × 1

Shop C
- Product C × 3

The cart must correctly calculate:

- Product subtotal
- Shop subtotal
- Discount
- Shipping
- Tax if supported
- Grand total

Do not trust totals sent from the frontend.

Recalculate important totals on the backend.

==================================================
11. MULTI-SHOP CHECKOUT
==================================================

This is one of the most important features.

A customer can checkout products from multiple shops in one transaction.

Example:

Customer buys:

Shop A:
- Product A = $20

Shop B:
- Product B = $30

Shop C:
- Product C = $50

Total = $100

The system must preserve shop ownership for each order item.

Recommended architecture:

Parent Order
    │
    ├── Shop Order A
    │      └── Items
    │
    ├── Shop Order B
    │      └── Items
    │
    └── Shop Order C
           └── Items

If the existing database architecture can support this more cleanly using
order_items.shop_id, use that approach where appropriate.

Do not create unnecessary duplicate order systems.

Choose the architecture that best fits the existing database.

==================================================
12. SHOP ORDER MANAGEMENT
==================================================

Shop owners must see only orders containing their products.

For example:

Customer order #1001:

Shop A:
- Product A

Shop B:
- Product B

Shop A should see:

Order #1001
→ Product A

Shop B should see:

Order #1001
→ Product B

Shop A must NOT see Shop B's private order information.

Super Admin can see the complete order.

==================================================
13. ORDER STATUS
==================================================

Support platform-level and shop-level order processing where necessary.

Example:

Parent Order:
paid

Shop A:
processing

Shop B:
shipped

Shop C:
delivered

Design the status system carefully so that one shop cannot incorrectly
change another shop's fulfillment status.

==================================================
14. PAYMENTS
==================================================

Payment belongs to the customer's overall checkout/order.

Do not duplicate payment unnecessarily for each shop.

However, the system must be able to determine:

- Total payment
- Shop subtotal
- Shop discounts
- Shop shipping
- Shop commission
- Shop payable amount

Never expose sensitive payment information.

Never store raw card numbers or CVV.

==================================================
15. SHOP COMMISSION
==================================================

Prepare the architecture for marketplace commissions.

For example:

Product price:
$100

Platform commission:
10%

Shop receives:
$90

The exact commission model should be configurable.

Possible models:

- Percentage
- Fixed fee
- Category-based commission
- Shop-specific commission

Create a proper commission structure rather than hard-coding percentages
inside controllers.

If payouts are not implemented yet, prepare the database/service architecture
for future payout functionality.

==================================================
16. SHOP PAYOUTS
==================================================

Prepare support for:

- Shop earnings
- Platform commission
- Pending balance
- Available balance
- Paid amount
- Payout history

Example:

Shop revenue:
$1,000

Platform commission:
$100

Shop balance:
$900

Do not implement real financial transfers unless the payment provider
integration exists.

The system should accurately record financial calculations.

==================================================
17. SHOP ADMIN DASHBOARD
==================================================

Create a separate dashboard for Shop Owners.

Shop dashboard should show only that shop's data:

- Total products
- Total orders
- Pending orders
- Processing orders
- Completed orders
- Revenue
- Sales
- Inventory
- Low stock
- Out of stock
- Recent orders
- Top products
- Reviews
- Earnings
- Commission
- Payout balance

Never show another shop's data.

==================================================
18. SUPER ADMIN DASHBOARD
==================================================

Super Admin dashboard should show platform-wide information:

- Total shops
- Active shops
- Pending shops
- Suspended shops
- Total customers
- Total products
- Total orders
- Total revenue
- Platform commission
- Shop earnings
- Pending payouts
- Top shops
- Top products
- Sales statistics
- Order statistics

Super Admin can filter statistics by:

- Shop
- Category
- Brand
- Date range
- Order status

==================================================
19. SHOP MANAGEMENT FOR SUPER ADMIN
==================================================

Create:

/admin/shops

Features:

- View shops
- Search shops
- Filter shops
- View shop details
- Approve shop
- Reject shop
- Suspend shop
- Activate shop
- View shop owner
- View shop products
- View shop orders
- View shop inventory
- View shop revenue
- View shop commission
- View shop reviews

Shop status changes must be authorized.

==================================================
20. SHOP REGISTRATION
==================================================

Prepare a shop registration workflow.

Example:

User
 ↓
Apply to become Shop Owner
 ↓
Create shop application
 ↓
Super Admin reviews
 ↓
Approve
 ↓
Shop becomes active

Possible application status:

pending
approved
rejected

If the existing project does not need public shop registration yet,
implement the backend architecture so it can be added later.

==================================================
21. SHOP PUBLIC PAGE
==================================================

Customers should be able to visit:

/shops/{slug}

Show:

- Shop logo
- Shop banner
- Shop name
- Description
- Rating
- Number of products
- Products
- Categories
- Reviews
- Shop status

Only active/public shops should be publicly visible.

==================================================
22. PRODUCT URL
==================================================

Products should be uniquely accessible.

Possible structure:

/shops/{shopSlug}/products/{productSlug}

or another clean SEO-friendly structure.

Make sure product ownership is still validated on the backend.

==================================================
23. REVIEWS
==================================================

Reviews should be connected to:

- Customer
- Product
- Shop
- Order item

Shop owners can moderate/manage reviews related to their products,
according to permissions.

Super Admin can manage all reviews.

Customers can review only products they actually purchased if that is the
business rule.

==================================================
24. COUPONS
==================================================

Decide whether coupons are:

Platform coupons
or
Shop coupons.

Support both if appropriate.

Example:

Platform coupon:
WELCOME10

Shop A coupon:
SHOPA10

A shop owner must not be able to create a platform-wide coupon unless
explicitly authorized.

Coupon validation must happen on the backend.

==================================================
25. SHIPPING
==================================================

Prepare multi-shop shipping.

A single checkout may contain products from:

Shop A
Shop B
Shop C

Shipping may therefore need to be calculated per shop.

Example:

Shop A shipping = $3
Shop B shipping = $5
Shop C shipping = $2

Total shipping = $10

Do not hard-code this.

Use the existing shipping architecture where possible.

==================================================
26. NOTIFICATIONS
==================================================

Notifications should respect user/shop ownership.

Shop Owner notifications:

- New shop order
- New review
- Low stock
- Out of stock
- Payment/order updates
- Payout updates

Super Admin:

- New shop application
- New shop
- Suspended shop
- Platform order
- Payment issues
- System alerts

Customer:

- Order updates
- Payment updates
- Shipping updates
- Review notifications

==================================================
27. REPORTS
==================================================

SUPER ADMIN reports:

- Platform sales
- Shop sales
- Commission
- Payouts
- Orders
- Customers
- Products
- Inventory

SHOP OWNER reports:

- Own sales
- Own orders
- Own products
- Own inventory
- Own revenue
- Own commission
- Own earnings

Never allow a Shop Owner to query another shop's report.

==================================================
28. DATABASE RELATIONSHIPS
==================================================

Review and update the database relationships.

At minimum consider:

User
 ├── owns Shops
 └── belongs to ShopUsers

Shop
 ├── owner
 ├── users/staff
 ├── products
 ├── orders/order items
 ├── inventory
 ├── reviews
 ├── coupons
 └── reports

Product
 ├── shop
 ├── category
 ├── brand
 ├── images
 ├── variants
 ├── inventory
 └── reviews

Order
 ├── customer
 ├── items
 ├── payments
 └── shop-related order data

==================================================
29. DATABASE MIGRATIONS
==================================================

Create proper Laravel migrations.

Do not manually modify the database without migrations.

Before changing existing tables:

1. Inspect current schema.
2. Determine dependencies.
3. Create safe migrations.
4. Preserve existing data.
5. Test migration.
6. Test rollback where practical.

If existing products/orders already exist, create a safe migration strategy
to assign existing records to an appropriate shop.

Do NOT silently delete existing data.

==================================================
30. API DESIGN
==================================================

Create clean API endpoints.

Example:

Super Admin:

GET    /api/admin/shops
POST   /api/admin/shops
GET    /api/admin/shops/{id}
PUT    /api/admin/shops/{id}
DELETE /api/admin/shops/{id}

Shop Owner:

GET    /api/shop
GET    /api/shop/products
POST   /api/shop/products
GET    /api/shop/products/{id}
PUT    /api/shop/products/{id}
DELETE /api/shop/products/{id}

Shop orders:

GET /api/shop/orders
GET /api/shop/orders/{id}

Shop inventory:

GET /api/shop/inventory
PUT /api/shop/inventory/{id}

Public:

GET /api/shops
GET /api/shops/{slug}
GET /api/shops/{slug}/products

Use the existing API naming conventions if they are already established.

Do not duplicate endpoints unnecessarily.

==================================================
31. FRONTEND ROUTING
==================================================

Create separate areas:

/admin/*
/shop/*
/customer/*

Example:

/admin/dashboard
/admin/shops
/admin/products
/admin/orders

/shop/dashboard
/shop/products
/shop/orders
/shop/inventory
/shop/reports
/shop/settings

/customer/orders
/customer/cart
/customer/checkout

Protect every route using proper authentication and authorization.

==================================================
32. FRONTEND SHOP CONTEXT
==================================================

If a user belongs to multiple shops, allow them to select the active shop.

Example:

Shop Selector:

[ Shop A ▼ ]

When Shop A is selected:

All shop dashboard data must use Shop A.

When Shop B is selected:

All shop dashboard data must switch to Shop B.

Do not rely only on a frontend variable for authorization.

Backend must still verify access to the selected shop.

==================================================
33. PERFORMANCE
==================================================

Optimize for potentially:

- 100 shops
- 1,000 shops
- 10,000+ shops
- Many products
- Many customers
- Many orders

Use:

- Database indexes
- Eager loading
- Pagination
- Query optimization
- Redis caching where safe
- Queues
- Lazy-loaded Vue routes
- Efficient API responses

Avoid N+1 queries.

Do not load every shop/product/order into memory.

==================================================
34. SECURITY
==================================================

Perform a complete multi-tenant authorization audit.

Test:

Shop A → Shop B product access
Shop A → Shop B order access
Shop A → Shop B inventory access
Shop A → Shop B reports
Shop A → Shop B customer information
Shop A → Shop B coupon access

Every unauthorized request must return 403 or appropriate response.

Check for:

- IDOR
- Mass assignment
- SQL injection
- XSS
- Authentication bypass
- Authorization bypass
- File upload vulnerabilities
- Rate limiting
- Privilege escalation

The most important requirement:

A Shop Owner must NEVER be able to access another shop's private data by
changing an ID in the URL or request body.

==================================================
35. TESTING
==================================================

Create/test:

Authentication tests
Authorization tests
Shop CRUD tests
Shop staff tests
Product ownership tests
Inventory ownership tests
Order ownership tests
Coupon tests
Review tests
Report tests
Commission tests
Multi-shop cart tests
Multi-shop checkout tests

Important security test:

Login as Shop A.

Attempt:

GET /api/shop/products/{Shop B Product ID}

Expected:

403 Forbidden

Repeat this for:

- Products
- Orders
- Inventory
- Coupons
- Reports
- Reviews
- Customers

==================================================
36. EXISTING DATA MIGRATION
==================================================

If the current project already contains:

products
orders
inventory
reviews
coupons

do NOT delete them.

Create a migration strategy.

For example:

Create a default shop:

"Default Shop"

Then assign existing products and related records to that shop where
appropriate.

Document all migration decisions.

==================================================
37. ADMIN UI
==================================================

Keep the existing professional design.

Requirements:

- Bootstrap
- Bootstrap Icons
- Responsive
- Clean cards
- Clean tables
- Good spacing
- Loading states
- Empty states
- Error states
- Toast notifications
- Confirmation dialogs

Do NOT use:

- Gradients
- Emoji
- Excessive animations
- Unnecessary decoration

The dashboard should look like a professional marketplace administration
system.

==================================================
38. DOCKER
==================================================

Verify everything works with the existing Docker setup.

Check:

docker compose ps

Verify:

- Laravel
- Vue
- MySQL
- Redis
- Nginx
- phpMyAdmin

Run migrations inside the correct container.

Check Laravel logs and frontend logs.

Do not bypass Docker just to make the feature work locally.

==================================================
39. FINAL MULTI-SHOP WORKFLOW TEST
==================================================

Perform this complete test:

1. Create Shop A
2. Create Shop B
3. Create Shop C

4. Create products:

Shop A → Product A
Shop B → Product B
Shop C → Product C

5. Create inventory for each shop.

6. Create customer.

7. Add Product A to cart.

8. Add Product B to cart.

9. Add Product C to cart.

10. Checkout all products.

11. Verify order data.

12. Verify each shop sees only its own products/order items.

13. Verify Super Admin sees everything.

14. Verify inventory decreases correctly.

15. Verify payment total.

16. Verify shipping calculation.

17. Verify commission.

18. Verify shop earnings.

19. Verify customer order history.

20. Verify notifications.

21. Verify reports.

22. Attempt unauthorized Shop A → Shop B access.

23. Confirm access is denied.

==================================================
40. FINAL REQUIREMENT
==================================================

The final system must be a REAL multi-shop marketplace.

Architecture:

                    SUPER ADMIN
                         │
          ┌──────────────┼──────────────┐
          │              │              │
        SHOP A         SHOP B         SHOP C
          │              │              │
       Products       Products       Products
       Inventory      Inventory      Inventory
       Orders         Orders         Orders
          │              │              │
          └──────────────┼──────────────┘
                         │
                      CUSTOMER
                         │
                    MULTI-SHOP CART
                         │
                  MULTI-SHOP CHECKOUT
                         │
                    PAYMENT / ORDER
                         │
              SHOP FULFILLMENT + COMMISSION

Do not simply add a shop_id column and stop.

Implement the complete architecture:

Database
+
Laravel Models
+
Relationships
+
Migrations
+
Policies
+
Services
+
API
+
Authentication
+
Authorization
+
Vue Pages
+
Pinia Stores
+
Routing
+
Admin Dashboard
+
Shop Dashboard
+
Multi-Shop Cart
+
Multi-Shop Checkout
+
Inventory
+
Orders
+
Payments
+
Commission
+
Reports
+
Notifications
+
Security
+
Testing

At the end, provide a detailed report of:

1. Files changed
2. Files created
3. Database migrations
4. New tables
5. Changed tables
6. New relationships
7. New API endpoints
8. New frontend routes
9. New Vue components
10. New Pinia stores
11. Authorization rules
12. Security fixes
13. Tests performed
14. Bugs found and fixed
15. Remaining issues

Do not claim success unless the features have actually been tested.


