# Multi-Branch (Multi-Shop) Upgrade Plan

*Status: PLANNING · Date: 2026-09-25 · Decision: "Shop" = physical/regional branches of ONE brand (e.g. X Store @ AEON, X Store @ Sovanna), unified under a single Super Admin.*

This plan follows `docs/multiple-shops.md`. It is the migration/implementation plan the spec requires to be produced **before** major changes.

---

## 1. Current state (verified against code, not assumptions)

- **No `Shop` model/table, no `shops` migration, no `shop_id` column anywhere** (backend or frontend). Fully greenfield for the shop concept.
- **Single store concept today** = the global `settings` key/value table (`storeName` = 'E-KHMER'), managed by `SettingsController`. That is the only "store" surface.
- **Orders are single-platform:** one `orders` + `order_items` (with `product_id`/`product_variant_id`), no shop concept.
- **Inventory** is per `product_variant_id` (global, one row per variant), with `inventory_transactions` ledger.
- **RBAC is binary:** `users.role` is a string column (`customer` | `admin`), enforced by the `auth:sanctum` + `admin` route middleware (`EnsureUserIsAdmin`). No roles/table, no Policies/Gates, no permission package.
- **Frontend:** single `/shop` catalog page (the storefront), `/admin/*` dashboard area, `/account/*` customer area. No `/shop/*` owner area, no Shop/Vendor types, no shop store.
- **Global unique constraints that will need per-branch re-scoping** (current list): `products.slug`, `products.sku`, `product_variants.sku`, `categories.slug`, `brands.slug`, `shipping_methods.code`, `coupons.code`, `settings.key` (global — stays).

## 2. Architectural decisions (from Q&A with product owner)

1. **"Shop" model = regional branches of one brand** — NOT independent vendors. Super Admin manages every branch; branch staff are scoped to one branch.
2. **Order ownership → single `orders` + `order_items.shop_id`.** Because branches are one brand and a customer can buy from multiple branches in one checkout, we keep ONE parent order; each `order_item` records which branch fulfilled it (snapshot pattern already exists: `order_items` snapshots product_name/sku/price). This preserves every existing query/report and avoids a parent/sub-order split that the data does not need.
3. **Categories & brands stay GLOBAL** (shared catalog taxonomy across all branches — decision taken).
4. **Branches scope:** a customer's cart can mix items from several branches; checkout keeps a single payment (per spec §14) while order_items remember each branch for per-branch fulfillment.

## 3. Scope of this pass (backend-first, test-driven, non-destructive)

### 3.1 Migrations (new files only — no existing table drops)
- **`create_shops_table`** — `shops`: id, name, slug (unique), code (unique, e.g. `AEON`), branch_type?, description, phone, email, address fields (mall, floor, address_line, city, province, postal_code, country, lat/lng), currency?, status (`pending|active|suspended|rejected|closed`), is_default bool, timestamps, softDeletes.
- **`create_shop_users_table`** — shop membership/staff: id, shop_id (FK), user_id (FK), role_in_shop (`owner|manager|staff`), status (`active|suspended`), timestamps. Composite unique (shop_id, user_id). A user may belong to multiple branches only if business rules allow.
- **`add_shop_id_to_products_table`** — nullable `shop_id` FK + index on products. Existing products stay unassigned (nullable) or auto-renamed to a default branch; per spec §36 we do NOT delete existing data — decide via seeder that assigns existing products to the default/primary branch.
- **`add_shop_id_to_order_items_table`** — `shop_id` FK + index on order_items (branch that fulfilled this line).
- **`add_shop_id_to_inventories_table`** — `shop_id` FK + index → this is where **per-branch stock lives** (Shop A iPhone=20, Shop B iPhone=50 independent). **NOTE (design decision):** the spec's inventory example (same product, different stock per branch) is best modeled at the *inventory* level, not by duplicating the product. We add `shop_id` to `inventories` (and `inventory_transactions`) so the shared catalog keeps ONE product row while each branch holds its own stock. This avoids UNNECESSARY product duplication for a single-brand chain and matches the spec's own example (both branches sell "iPhone", quantities differ).
  - Migration note: `inventories` currently has a unique on `product_variant_id`. That unique must become composite `(product_variant_id, shop_id)`.
- **`add_shop_id_to_shipping_methods_table`** — nullable `shop_id` (global methods optional), per-spec §25 per-shop shipping where needed.
- **`add_shop_id_to_coupons_table`** — nullable `shop_id` (platform-wide coupon when null; per-branch otherwise), per spec §24.
- **`add_shop_id_to_categories?`** — NOT in this pass (global categories, decision taken).
- **`create_reports_*` / payout/commission tables** — NOT in this pass. Spec §15/§16 says prepare architecture but do not implement financials without a payment provider; we add a `commission_rate` (decimal) column on `shops` so the architecture is future-ready, but no payout tables.

### 3.2 Models & relations
- **`Shop` model** — `users()` belongsToMany via shop_users (pivot with `role_in_shop`/`status`), `products()` hasMany, `orderItems()` hasMany through OrderItem.shop_id, `inventories()` hasMany, `shippingMethods()` hasMany, `coupons()` hasMany. Scopes: `active`, `primary/default`. `slug` validation.
- **`User`** — add `shops()` belongsToMany(Shop, 'shop_users') with pivot. Add helper `isShopStaffOf(Shop)` / `belongsToShop()`.
- **`Product`** — belongsTo Shop (nullable).
- **`OrderItem`** — belongsTo Shop (nullable until backfilled).
- **`Inventory`** — belongsTo Shop; unique `(product_variant_id, shop_id)`; `scopeForShop()`.
- **`Order`** — hasMany OrderItems → cached `shops()` distinct helper for per-branch sales.
- **`Coupon`/`ShippingMethod`** — nullable belongsTo Shop.

### 3.3 Authorization (spec's hard requirement — "Shop A must NEVER touch Shop B")
- **Add Policies** (`app/Policies/` — currently empty): `ShopPolicy`, `ProductPolicy`, `OrderItemPolicy`, `InventoryPolicy`. Registered via `Gate`/`AuthServiceProvider`.
- **Ownership guard (backend, not frontend):** every admin/shop endpoint re-checks `shop_id` from the authenticated context; 403 on cross-shop access. The spec explicitly forbids trusting `shop_id` from the request body/frontend.
- `EnsureUserIsAdmin` stays for super-admin; new `EnsureShopStaff`/`EnsureShopAccess` middleware (or per-route policy) for branch-scoped staff.

### 3.4 API (new route groups, minimal new endpoints; follow existing conventions)
- Public branch pages: `GET /api/shops`, `GET /api/shops/{slug}`, `GET /api/shops/{slug}/products` (per spec §21/§30).
- Admin branch management: `/api/admin/shops` CRUD (super admin only) — backing for the "Shop Management for Super Admin" feature.
- Branch-scoped shop-owner endpoints: `/api/shop/*` (products/orders/inventory/reports) — only for authenticated staff of that branch; spec §30 + §12 (each branch sees only its own orders/items).
- Every endpoint: auth + role check + shop ownership check. Tests enforce 403 cross-branch (spec §35).

### 3.5 Frontend (later pass, after backend is green)
- `/shop/:slug` public branch page; shop-context store (Pinia) for branch selection; extend `/admin/*` with a branch-scoped Shop Owner area per spec §31/§32; i18n keys added. NOT in this backend-first pass (keeps PRs reviewable; frontend build stays green).

## 4. Data migration strategy (non-destructive, per spec §36)
- After migrations, run a seeder/command `AssignToDefaultShop` that:
  1. Creates (or reuses) the default branch ("E-KHMER Main Store" / slug `main`, code `MAIN`, `is_default=1`).
  2. Sets `products.shop_id`, `order_items.shop_id`, `inventories.shop_id` for every existing row whose shop_id is null → default branch.
  3. Leaves a documented data-migration report (counts before/after).
- No existing records are deleted. Existing global `settings` remain the platform default; per-branch settings can override later.

## 5. Testing
- Feature tests: shop CRUD (super admin), cross-branch product/order/inventory access → **403**, staff can manage only own branch, multi-branch cart + single-payment checkout, per-branch order filtering (spec §12 example: customer order #1001 → Shop A sees only Shop A line, Shop B sees only Shop B line, Super Admin sees all), inventory isolation (Shop A iPhone=20 / Shop B iPhone=50).
- Existing suite must stay green: currently **132 passed / 626 assertions**.

## 6. Deliverables & report
- On completion: docs report listing files changed/created, migrations, new tables, new relationships, new API endpoints, authorization rules, security fixes, tests performed, remaining issues (per spec §40).

## 7. Open items / out of scope for this pass
- Independent vendor registration, payouts, platform commission (spec §16/§20) — architecture-only (commission_rate column), not functional, until a payment provider exists.
- Frontend multi-branch UI + i18n (next pass).
- Per-branch settings; per-branch category/brand override (stayed global by decision).
