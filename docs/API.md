# API Reference

The backend exposes a JSON REST API rooted at `http://localhost:8000/api`. All routes are declared in `backend/routes/api.php`.

- **Base URL:** `http://localhost:8000/api`
- **Format:** JSON
- **Auth:** Laravel Sanctum bearer tokens (`Authorization: Bearer <token>`)

The frontend communicates with this API via a single Axios instance (`frontend/src/api/client.ts`) configured with `VITE_API_URL`.

---

## 1. Authentication

| Method | Endpoint | Description | Auth |
| --- | --- | --- | --- |
| POST | `/auth/register` | Register a new customer | Public |
| POST | `/auth/login` | Login, returns access token | Public |
| GET | `/auth/me` | Current authenticated user | Bearer |
| POST | `/auth/logout` | Revoke current token | Bearer |

**Login**
```json
// request
{ "email": "customer@example.com", "password": "secret" }
```
```json
// response
{ "access_token": "...", "token_type": "Bearer", "user": { ... } }
```

---

## 2. Public Catalog & Lookup

### Catalog

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/catalog/products` | Paginated/filtered product listing |
| GET | `/catalog/featured` | Featured products |
| GET | `/catalog/facets` | Available filters (facets) for the filter sidebar |
| GET | `/catalog/products/{slug}` | Product detail by slug |

### Lookup endpoints (public)

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/categories` | Category tree/list |
| GET | `/brands` | Brand list |
| GET | `/attributes` | Product attributes + values (for variant filtering) |
| GET | `/shipping-methods` | Available shipping methods |
| GET | `/products/{product}/reviews` | Approved reviews for a product |
| POST | `/coupons/validate` | Validate a coupon code (returns eligibility) |

### Cart (public, no auth required for the cart itself)

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/cart` | Current cart contents |
| POST | `/cart` | Add item to cart |
| PUT | `/cart/items/{cartItem}` | Update item quantity |
| DELETE | `/cart/items/{cartItem}` | Remove item |
| DELETE | `/cart` | Clear cart |
| GET | `/cart/totals` | Subtotal, discount, tax, total |

### Checkout (begins an order; reservation lock applied)

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/checkout` | Begin checkout (reserve inventory, create pending order) |
| POST | `/checkout/{orderNumber}/confirm` | Confirm payment → deduct reserved stock |
| POST | `/checkout/{orderNumber}/cancel` | Cancel → release reserved stock |

---

## 3. Customer (Authenticated)

### Orders

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/orders` | List the customer's orders |
| GET | `/orders/{orderNumber}` | Order detail (with items, payments, shipment) |
| POST | `/orders/{orderNumber}/cancel` | Cancel an order |

### Wishlist

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/wishlist` | List wishlist items |
| POST | `/wishlist` | Add a product |
| DELETE | `/wishlist/{product}` | Remove a product |

### Addresses

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/addresses` | List addresses |
| POST | `/addresses` | Create address |
| PUT | `/addresses/{address}` | Update address |
| DELETE | `/addresses/{address}` | Delete address |
| POST | `/addresses/{address}/default` | Set as default |

### Reviews

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/reviews` | Create a review (enforced: only for `Delivered` orders) |
| PUT | `/reviews/{review}` | Update own review |

### Account & Profile

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/account/dashboard` | Customer dashboard summary |
| PUT | `/account/profile` | Update profile |
| POST | `/account/password` | Change password |
| GET | `/account/notifications` | List notifications |
| POST | `/account/notifications/{notification}/read` | Mark notification read |

---

## 4. Admin (Authenticated + `admin` role)

All admin routes are guarded by `auth:sanctum` **and** the `admin` middleware.

### Dashboard & Reports

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/dashboard/overview` | KPIs: revenue, orders, customers, low-stock alerts |
| GET | `/admin/reports/orders.csv` | Export orders report as CSV |

### Products

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/products` | List products |
| POST | `/admin/products` | Create product (with variants) |
| GET | `/admin/products/{product}` | Product detail |
| PUT | `/admin/products/{product}` | Update product |
| DELETE | `/admin/products/{product}` | Delete product |

### Categories

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/categories` | List categories |
| POST | `/admin/categories` | Create category |
| PUT | `/admin/categories/{category}` | Update category |
| DELETE | `/admin/categories/{category}` | Delete category |

### Brands

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/brands` | List brands |
| POST | `/admin/brands` | Create brand |
| PUT | `/admin/brands/{brand}` | Update brand |
| DELETE | `/admin/brands/{brand}` | Delete brand |

### Shipping Methods

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/shipping-methods` | List |
| POST | `/admin/shipping-methods` | Create |
| PUT | `/admin/shipping-methods/{method}` | Update |
| DELETE | `/admin/shipping-methods/{method}` | Delete |

### Orders (fulfillment pipeline)

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/orders` | List all orders |
| GET | `/admin/orders/{order}` | Order detail |
| PUT | `/admin/orders/{order}/transition` | Advance state machine (`Pending → Confirmed → Processing → Shipped → Delivered`) |

### Coupons

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/coupons` | List |
| POST | `/admin/coupons` | Create |
| PUT | `/admin/coupons/{coupon}` | Update |
| DELETE | `/admin/coupons/{coupon}` | Delete |

### Review Moderation

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/reviews` | List reviews (incl. pending) |
| POST | `/admin/reviews/{review}/approve` | Approve review |
| POST | `/admin/reviews/{review}/reject` | Reject review |

### Customers

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/admin/customers` | List customers |
| GET | `/admin/customers/{user}` | Customer detail |

---

## 5. Models (`App\Models`)

| Model | Notes |
| --- | --- |
| `User` | Auth + role (customer/admin) |
| `Address` | Customer shipping/billing addresses |
| `Category`, `Brand` | Product taxonomy |
| `Product`, `ProductImage` | Product entity + media |
| `Attribute`, `AttributeValue` | EAV attribute definitions |
| `ProductVariant`, `VariantAttributeValue` | Concrete SKUs + their attribute bindings |
| `Inventory`, `InventoryTransaction` | Stock + ledger |
| `Cart`, `CartItem` | Shopping cart |
| `Wishlist`, `WishlistItem` | Customer wishlist |
| `ShippingMethod`, `Shipment` | Fulfillment |
| `Coupon`, `CouponUsage` | Promotions |
| `Order`, `OrderItem`, `Payment`, `PaymentTransaction` | Orders, line items, payments |
| `Review` | Product reviews |
| `Notification` | User notifications |

## 6. API Resources (`App\Http\Resources`)

Resources shape JSON responses and hide internal fields:

`AddressResource`, `AttributeResource`, `AttributeValueResource`, `BrandResource`, `CartItemResource`, `CartResource`, `CategoryResource`, `CouponResource`, `OrderItemResource`, `OrderListResource`, `OrderResource`, `ProductDetailResource`, `ProductResource`, `ReviewResource`, `ShippingMethodResource`, `UserResource`.

## 7. Validation Requests (`App\Http\Requests`)

Each endpoint validates its payload through a Form Request: `LoginRequest`, `RegisterRequest`, `UpdateProfileRequest`, `ChangePasswordRequest`, `AddressRequest`, `CartAddRequest`, `CartUpdateRequest`, `CheckoutRequest`, `CouponValidateRequest`, `ReviewStoreRequest`, `ReviewUpdateRequest`, `OrderTransitionRequest`, `AdminProductRequest`, `AdminCategoryRequest`, `AdminBrandRequest`, `AdminShippingMethodRequest`, `AdminCouponRequest`.

## 8. Business-Logic Services (`App\Services`)

| Service | Responsibility |
| --- | --- |
| `AuthService`-style concerns handled by controllers | Thin controllers, see below |
| `CatalogService` | Dynamic variant filtering, facets, featured |
| `CartService` | Cart add/update/remove/totals |
| `CheckoutService` | Begin/confirm/cancel checkout, inventory reservation |
| `InventoryService` | Reserve/release/deduct stock, ledger |
| `CouponService` | Coupon rule validation + application |
| `OrderService` | Order lifecycle state machine |
| `ReviewService` | Verified-review enforcement + moderation |
| `DashboardService` | Admin KPI/aggregates |
| `OrderNumberGenerator` | Human-readable unique order numbers |

## 9. Error handling & auth notes

- Unauthenticated requests to protected routes return `401`; the frontend automatically clears stored tokens on 401.
- Admin routes return `403` for non-admin users.
- Business-rule violations (invalid coupon, insufficient stock, invalid order transition, unverified review) return appropriate `4xx` responses with a message the UI can display.
