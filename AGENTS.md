# AI Agent Guidelines for E-Commerce Project

## 🎯 Project Overview
This is a full-stack E-Commerce Management System featuring:
- **Backend**: Laravel REST API (PHP 8.3)
- **Frontend**: Vue 3 + TypeScript (Vite, Pinia, Vue Router)
- **Database Engine**: MySQL 8
- **Cache/Session**: Redis
- **Infra/DevOps**: Docker, Docker Compose, Nginx

## 📁 Project Structure
- `/backend/`: Laravel API application. All backend code, tests, and API routes reside here.
- `/frontend/`: Vue 3 TypeScript application. All UI components, state management, and routing reside here.
- `/docker/`: Docker configurations for Nginx and PHP.
- `docker-compose.yml`: Main deployment and development service definitions (app, frontend, nginx, mysql, redis, phpmyadmin).

## 🛠️ Technology Stack Rules

### Backend (Laravel)
- Follow standard Laravel conventions and directory structures (Controllers in `app/Http/Controllers`, Models in `app/Models`, etc.).
- Use API Resources for JSON responses.
- Implement business logic in Service classes if it becomes too complex for Controllers.
- All endpoints must be secured using Laravel Sanctum where applicable.
- Database queries should utilize Eloquent ORM. Do not write raw SQL unless absolutely necessary for performance optimization.

### Frontend (Vue 3 + TypeScript)
- Use Composition API with `<script setup>`.
- Enforce strict typing with TypeScript for props, emits, and API responses.
- Manage global state via Pinia.
- Styling should primarily utilize Bootstrap 5 classes.
- API interactions should be done using Axios and configured with the `VITE_API_URL` environment variable.

## 🐳 Docker Workflow
When interacting with the system, always use the provided Docker containers. Do not run PHP, Node, or Composer commands on the host machine.
- Start environment: `docker compose up -d`
- Run Laravel artisan commands: `docker compose exec app php artisan <command>`
- Run Composer: `docker compose exec app composer <command>`
- Run npm commands: `docker compose exec frontend npm <command>`

## 🗄️ Database
- Ensure migrations reflect all schema changes (`docker compose exec app php artisan migrate`).
- Do not make manual schema changes without an accompanying migration file.
- The app connects to MySQL via the `mysql` host internally.

## 🔒 Security Practices
- Never hardcode credentials, API keys, or JWT secrets in the code.
- Ensure sensitive environment files (`.env`, `backend/.env`, `frontend/.env`) remain untracked.

## 📝 Committing Code
Follow conventional commits when summarizing changes, targeting the appropriate branch (`main`, `develop`, or `feature/*`).



AGENDA: Full-Stack E-Commerce Architecture1. Frontend UI/UX Architecture & ScreensCustomer Web InterfaceAuth & Profile: Login, Registration, Password Reset, Customer Dashboard, Address Manager.Browsing & Discovery: Home Page, Product Listing (with dynamic Filter Sidebar & Sorting), Product Details (Variant Selector, Gallery, Zoom, Stock Badge).Shopping Pipeline: Cart Drawer/Page, Multi-Step Checkout (Address, Shipping, Coupon, Payment), Order Success/Confirmation.Post-Purchase & Engagement: Order History & Tracking Stepper, Wishlist, Moderated Product Reviews.Admin Dashboard InterfaceOverview: KPI Cards (Revenue, Orders, Customers), Revenue Charts, Recent Orders Table, Low-Stock Alerts.Catalog & Stock Management: Product/Variant CRUD, Tree-view Category Manager, Brand Manager, Inventory Ledger.Fulfillment & Commerce: Order Processing Pipeline, Payment Receipts, Shipping Method Config, Coupon Creator, Review Moderation Queue, PDF/CSV Reports.2. Backend Database Tables & RelationshipsCore Entities & Relations:[ users ] 1───< [ addresses ]
   │
   ├───1───< [ orders ] 1───< [ order_items ] >───1 [ product_variants ]
   ├───1───< [ carts ]  1───< [ cart_items ]  >───1 [ product_variants ]
   ├───1───< [ wishlists ] 1─< [ wishlist_items ] >─1 [ products ]
   └───1───< [ reviews ]

[ products ] 1───< [ product_variants ] 1───< [ inventories ]
   │                     │
   ├───> [ categories ]  └───< [ variant_attribute_values ] >───1 [ attribute_values ]
   └───> [ brands ]
User & Auth Module:users $(1:N) \rightarrow$ addressesProduct Catalog & EAV (Entity-Attribute-Value) Module:categories $(1:N) \rightarrow$ productsbrands $(1:N) \rightarrow$ productsproducts $(1:N) \rightarrow$ product_imagesproducts $(1:N) \rightarrow$ product_variantsattributes $(1:N) \rightarrow$ attribute_valuesproduct_variants $(1:N) \rightarrow$ variant_attribute_values $\leftarrow(N:1)$ attribute_valuesInventory & Cart Module:product_variants $(1:1) \rightarrow$ inventoriesinventories $(1:N) \rightarrow$ inventory_transactionsusers $(1:1) \rightarrow$ carts $(1:N) \rightarrow$ cart_items $\leftarrow(N:1)$ product_variantsusers $(1:1) \rightarrow$ wishlists $(1:N) \rightarrow$ wishlist_items $\leftarrow(N:1)$ productsOrder Processing & Fulfillment Module:users $(1:N) \rightarrow$ orders $(1:N) \rightarrow$ order_items $\leftarrow(N:1)$ product_variantsorders $(1:1) \rightarrow$ payments $(1:N) \rightarrow$ payment_transactionsorders $(1:1) \rightarrow$ shipments $\leftarrow(N:1)$ shipping_methodscoupons $(1:N) \rightarrow$ coupon_usages $\leftarrow(N:1)$ ordersusers $(1:N) \rightarrow$ reviews $\leftarrow(N:1)$ productsusers $(1:N) \rightarrow$ notifications3. Core Business Logic EngineAuthentication & Authorization:JWT auth with Refresh/Access token rotation.Role-Based Access Control (RBAC): Customer vs Admin.Dynamic Variant Filtering:SQL joins mapping selected attribute_values (e.g., Size: L, Color: Blue) to resolve specific product_variant_ids and real-time stock levels.Inventory Reservation Lock:Atomic locking mechanism: when a user initiates checkout, reserve quantity in inventories for $15$ minutes. Release back if payment fails/expires; deduct permanently on payment success.Order Lifecycle State Machine:Strict status progression:$$\text{Pending} \longrightarrow \text{Confirmed} \longrightarrow \text{Processing} \longrightarrow \text{Shipped} \longrightarrow \text{Delivered}$$Snapshotting rule: Store item price, title, and variant details directly inside order_items at checkout time to prevent catalog updates from altering historic order logs.Promotion & Discount Engine:Validate coupon rules: Expiry Date check $\rightarrow$ Usage Limit check $\rightarrow$ Minimum Order Amount validation $\rightarrow$ Apply Percentage or Fixed Amount discount to order subtotal.Verified Review Authorization:Enforce DB verification ensuring reviews.user_id has a corresponding orders record containing the product with status = Delivered before permitting review creation.