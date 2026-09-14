# Figma UI Prototype Prompts — E-KHMER

This document contains copy‑paste prompts for generating a UI prototype of the **E‑KHMER** e‑commerce system in Figma using **Make Designs / Figma AI** (also works with any text‑to‑UI tool such as v0, Galileo, or Lovable).

> **How to use**
> 1. Paste the **Master Style Prompt** below into the "Design system / App" input first (it keeps every generated screen consistent).
> 2. Then paste the prompt for one **screen at a time** as your page description.
> 3. Ask for `Desktop 1440px` and `Mobile 390px` frames, plus `Light` and `Dark` variants.
> 4. Set the app to **E‑KHMER** so names, tone, and flows stay consistent.

---

## 1. Master Style Prompt (paste once)

> Design a modern, premium e‑commerce web application called **"E‑KHMER"** — a Cambodian online retail store with full storefront, customer account area, and admin console. Use a clean, contemporary SaaS aesthetic. Light and dark mode both required.
>
> **Color tokens**
> - Primary: Electric blue `#2563EB` (hover/dark `#1E40A8`; in dark mode `#3B82F6`)
> - Accent: Amber/gold `#FBBF24` (dark `#FACC15`) — used for badges, sale tags, highlights, gradients
> - Success: Emerald `#10B981` (dark `#34D399`)
> - Danger: Red `#EF4444`
> - Light mode backgrounds: canvas `#F9FAFB`, surface/cards `#FFFFFF`, borders `#E5E7EB`, body text `#111827`, muted text `#6B7280`
> - Dark mode backgrounds: canvas `#0B0F19` (midnight navy), surface/cards `#1E293B` (dark slate), borders `#334155`, body text `#F8FAFC`, muted `#94A3B8`
>
> **Typography**
> - Font: **Inter** for Latin; **Kantumruy Pro** as fallback for Khmer text (Khmer locale needs generous line‑height ~1.75 so diacritics aren't clipped)
> - Headings: extrabold (800), tight tracking; H1 up to 3.4rem on hero, H2 2–3rem; section labels are 11–12px bold uppercase with wide letter‑spacing in primary blue
>
> **Components & patterns**
> - Cards: 12px rounded corners, 1px border (`#E5E7EB`), soft drop shadow (`0 1px 3px rgba(17,24,39,.08)`); hover lifts with stronger shadow; feature‑glow cards get a subtle blue radial glow on their top‑left in dark mode
> - Buttons: `btn-primary` solid blue, `btn-secondary` outlined card, `btn-outline` blue outlined, `btn-danger` soft red, `btn-accent` amber with dark text; sizes sm (12px), default (14px), lg (16px); icon‑only 40×40 ghost buttons for header actions
> - Forms: inputs 12px radius, 1px border, surface background, blue 2px focus ring at 20% opacity, error state red border
> - Chips/pills: small rounded tags for statuses, counts, and filter summaries
> - Hero gradient: deep blue `#1E3A8A` → midnight navy `#0F172A` with an electric‑blue radial glow at the top‑right; decorative blurred circles; accent for CTAs and highlighted text
> - Rounded imagery: product/square images use `aspect-ratio 1:1`, `object-cover`, subtle scale‑up on hover; category tiles get a black‑40% bottom gradient overlay
> - Icon set: **Lucide** style, thin 1.5–2px strokes (heart, shopping bag, search, user, truck, refresh, shield, headset, star, etc.)
> - Layout width: centered container max‑width 1280px with 16–32px gutters; 24–32px section vertical spacing
>
> **Breakpoints**: Desktop 1440px, Tablet 768px, Mobile 390px. On mobile the header collapses to an icon + hamburger, sidebars become slide‑in drawers, grids go 2‑column.
>
> **Branding**: Logo is the text mark **"E‑KHMER"** where "E‑" is primary blue and "KHMER" is body ink. Admin/account sidebars show a small blue rounded square "EK" mark. Identity chip tagline: "ADMIN PANEL", "MY ACCOUNT", "STORE".
>
> Use placholder product photography (premium lifestyle product shots on neutral backgrounds), modern imagery, and realistic English copy. Keep layouts airy with generous white space.

---

## 2. Storefront Screens (Customer)

### 2.1 Header & Footer (shared frame)
> Design the global storefront header and footer for E‑KHMER.
> **Header**: top announcement bar (dark navy `#1E40A8`, ~14px tall) reading "FREE SHIPPING ON ORDERS OVER $100 · EXTRA 20% OFF FIRST ORDER". Below it a sticky header: hamburger (mobile only), E‑KHMER logo (blue "E‑" + ink "KHMER", extrabold), centered search bar with search icon, then right‑side icon cluster: language switcher (dropdown, EN/KH), dark‑mode toggle (sun/moon), sign‑in icon (for guests), wishlist heart icon with small red/blue count badge, shopping‑bag cart icon with count badge, and an avatar/profile icon that opens a user dropdown (name, email, links: Dashboard, My Orders, Wishlist, Addresses, Profile, and red Sign out). Below on desktop a category nav rail (horizontal, e.g. Electronics, Fashion, Shoes, Beauty, Watches, Home & Living).
> **Footer**: multi‑column — brand + short description + social icons, "Shop" links, "Customer Service" links (Help Center, Returns, Shipping, Track Order), "Company" links, and a Newsletter block. Bottom bar with copyright + payment icons. Full dark‑mode variant included.

### 2.2 Home Page
> Design the E‑KHMER home page.
> Section 1 — **Hero**: full‑width premium gradient (deep blue → midnight navy) with electric‑blue radial glow and blurred accent circles. Left: small pill badge "★ NEW SEASON COLLECTION" (amber sparkle icon), huge headline 2 lines — second line in amber‑to‑gold gradient text — short subtitle, two CTAs: solid amber "Shop Now →" and ghost white outlined "Explore Offers →". Below: divider with 5‑star rating + "4.9/5 from 12k reviews", "50k+ Happy Customers", "99% On‑time Delivery". Right (desktop): layered floating visuals — two rotated product photos, a white free‑shipping card, a 5‑star product review card, and a floating amber "‑22% today only" tag.
> Section 2 — **Benefits strip**: 4 equal icons in primary‑blue circles: Free Shipping, 30‑Day Returns, Secure Payment, 24/7 Support.
> Section 3 — **Shop by Category**: eyebrow label + 2‑line heading + subtitle with "All categories →" link; 6 rounded category tiles (image, dark bottom gradient, icon chip + category name in footer of each tile).
> Section 4 — **Featured Products**: horizontal carousel rail of product cards with arrows (see Product Card spec below).
> Section 5 — **Deal of the Day**: gradient banner split 2‑column — left: "DEAL OF THE DAY" pill with clock icon, product name, description, huge price + strikethrough compare price + amber % badge, live countdown tiles (Days/Hours/Mins/Secs in translucent boxes), amber "Shop the deal" button; right: product photo with blue gradient overlay + "Ends tonight · While stock lasts" glass card.
> Section 6 — **Best Sellers**: product rail again.
> Section 7 — **Brand strip**: 6–7 outlined brand‑name chips.
> Section 8 — **New Arrivals**: 4‑column product grid (see product card).
> Section 9 — **Testimonials**: 3 cards, star rating, italic quote, avatar initials circle + verified‑buyer badge with blue check.
> Section 10 — **Newsletter**: gradient rounded banner, send icon, headline "Get 20% off your first order", checkbox perks list, email input + amber subscribe button. Product card spec: square image, amber "-X%" badge top‑left + blue "NEW" badge, white circular wishlist heart top‑right, brand eyebrow in blue caps, 2‑line title, 5‑star rating + review count, price + strikethrough compare price, color‑swatch dots, full‑width blue "Add to Cart" button; disabled state reads "Out of Stock".

### 2.3 Shop / Product Listing (with Filters)
> Design the E‑KHMER shop listing page.
> Top: breadcrumb (Home / Shop / Category), title "Shop Products", result count line "Showing 24 of 240 Products".
> Below: a tool bar card with a mobile‑only "Filters" button (sliders icon) and removable filter chips (e.g. "Electronics ×", "Adidas ×"), right‑aligned sort dropdown (Featured, Price Low→High, Price High→Low, Top Rated).
> Layout: **left sidebar 260px** card containing filter groups stacked with clear section labels — Category (radio list w/ count badge), Brand (radio list w/ counts), Price Range (dual range sliders + two price chips like `$0` – `$450`), Rating (radio rows showing 4.5★ & up, 4.0★ & up, 3.5★ & up, Any), Color (round 32px swatch buttons, selected gets blue ring), Size (pill buttons S/M/L/XL, selected filled), "In stock only" checkbox, and a full‑width blue‑outline "Clear All" button.
> Main area: responsive product grid (2 / 3 / 4 columns), loading skeleton state, empty state with "No products found" icon + "Clear Filters" CTA, and a pagination bar (page numbers + prev/next, showing "Page 1 of 5").
> **Mobile variant**: same filters inside a right‑slide drawer (320px) with "Clear All" + "Apply" buttons at the bottom.

### 2.4 Product Details (PDP)
> Design the E‑KHMER product details page.
> Breadcrumb: Home / Category / Product name (truncated).
> Two‑column layout (image left, info right, `1fr/1fr`):
> **Left — Gallery**: large square main image in a bordered card with hover‑zoom (cursor magnifier icon top‑right when hovering) and an amber "-X%" discount badge top‑left; clicking opens a fullscreen lightbox. Below a 4‑thumb strip, active thumb has blue 2px ring.
> **Right — Info**: brand eyebrow in blue uppercase; H1 product title; SKU gray line; star rating + "(x reviews)" + "Write a review" link; price row: large price + gray strikethrough compare price + "You save $X" amber chip; stock line with a status badge pill (In Stock green / Only X left amber / Out of Stock red) + "X available".
> **Variant selectors**: "Color" group = round swatches with blue ring when selected; "Size" group = pill buttons; live variant price/stock updates. Below: Quantity stepper (− / count / +) then a row of two buttons: full‑width blue "Add to Cart" and white‑outlined "Buy Now"; then a circular heart wishlist toggle.
> **Trust strip**: 4 mini rows — Free shipping, 30‑day returns, Secure checkout, Verified reviews.
> **Tabs** below: Description | Specifications (2‑col attribute list) | Reviews (X). Reviews tab: summary card — big average number + stars, rating breakdown bars (5→1 with % widths), list of review cards (avatar initials, name, star rating, date, verified badge, title, body), and a "Write a Review" modal form (rating picker, title, body) with a note that only verified buyers can review.
> **Related products** rail at bottom.
> **Mobile**: gallery full‑width on top, info below; sticky bottom bar with Add to Cart.

### 2.5 Cart
> Design the E‑KHMER cart page. Title "Shopping Cart", link back to shop.
> Main content: **cart line rows** — 96×72px product thumb, title, variant label ("Color: Blue · Size: M"), unit price, quantity stepper (− 2 +) with a remove (trash) icon, and line total. Below a coupon row: input + "Apply" button; a success message chip appears when the coupon is applied showing the discount.
> Right: **Order Summary card (380px, sticky)** — Subtotal, Discount (− red), Tax, Shipping ("FREE" in green or amount), divider, bold Total, "Proceed to Checkout" full‑width blue button, note "5 items · Free shipping on orders over $100", and a "Continue shopping →" link.
> Empty state: centered bag icon, "Your cart is empty", "Add some items before checking out", "Back to shop" button.
> **Cart Drawer variant**: a right slide‑in panel (400px) version of the same content with overlay backdrop, used when clicking the header bag icon.

### 2.6 Checkout (Multi‑Step)
> Design the E‑KHMER multi‑step checkout.
> **Stepper** (top): 1 Shipping → 2 Delivery → 3 Payment → 4 Review; completed steps show green circles with checkmarks, current step blue, connectors progress from gray to green.
> Main card (left, `1fr`) + sticky Order Summary (right, 380px).
> **Step 1 Shipping**: "Where should we deliver your order?"; grid of selectable address cards (label chip + "Default" green tag, name, 2 address lines, phone) with blue border + tint on selection; a dashed "＋ Add New Address" tile opens a modal form (Label, Full name, Line 1, Line 2, City, State, Postal code, Country, Phone → Save). "Continue to Delivery" blue button bottom‑right.
> **Step 2 Delivery**: selectable shipping rows — radio, method name + description + ETA chip ("3–4 days"), and price on the right; free options show amber "Free" chip.
> **Step 3 Payment**: radio rows — Cash on Delivery, Credit/Debit Card (Visa/Mastercard/Amex), Bank Transfer, Online Gateway (PayPal/Apple/Google Pay); card selection reveals a gray card field panel (Name on card, Card number, Expiry MM/YY, CVC); bank shows transfer instructions; gateway shows redirect note; lock icon + "Payments are encrypted and securely processed."
> **Step 4 Review**: 3 editable summary rows (Ship to / Delivery / Payment each with an "Edit" ghost button) + item list with thumbs, then full‑width "Place Order · $XXX.XX" blue button with lock icon.
> Order Summary: Subtotal, Discount (red), Tax, Shipping ("Calculated later" on step 1, "Free" in green or amount), divider, bold Total, item count note.

### 2.7 Order Success
> Design the E‑KHMER order confirmation page (centered, max 672px).
> Big green circle with checkmark icon; headline "Order Confirmed!"; "Thank you, [Name]" message.
> 3 info cards in a row: Order Number / Estimated Delivery (date) / Payment Method.
> Summary card: "Order Summary" + green status pill ("Confirmed"); line rows Subtotal → Total (bold); item rows with thumbs, title, qty, line total.
> Buttons: blue "Track My Order", outlined "Download Receipt" (file icon), outlined "Continue Shopping".
> Note: "A confirmation email was sent to you@email.com".

### 2.8 Order Tracking
> Design the E‑KHMER order tracking page.
> Card with order number + status pill. **Tracking stepper**: 5 nodes — Pending → Confirmed → Processing → Shipped → Delivered; completed / current nodes blue or green with check icons, future nodes gray; connector lines show progress. Each stage label plus timestamps.
> Below: order item thumbs row, delivery address card, and total summary.

---

## 3. Account Screens (Customer, "MY ACCOUNT")

> **Shared shell (design once, reuse):** left sidebar 288px on desktop with the E‑KHMER logo chip + "MY ACCOUNT" tag, then a profile card (avatar 48px circle with green verified‑check badge overlay, name, email), then nav with icons and counts: Dashboard, My Orders (count), Wishlist (count), Addresses, Profile, Notifications (count), My Reviews, Change Password, with "Back to store" and red "Sign out" below; active item = blue tinted row, blue left indicator bar, blue icon chip. Mobile = top header + slide‑in drawer. Content area: page title "Dashboard / My Orders …", language & theme toggles top‑right, amber "verify email" banner with Resend button when needed. Provide every page inside this shell.

### 3.1 Account Dashboard
> Design the E‑KHMER customer dashboard (inside account shell). Greeting "Hi, [Name]" subtitle. 2–3 overview cards: Total Orders, Total Spent, Wishlist Items. A "Recent Orders" table (Order number, date, total, status pill, action) with "View all" link. Button "Browse latest products →". Maybe a banner "You have X unread notifications".

### 3.2 My Orders
> Design the E‑KHMER order history list. Search + optional status filter chips. Order cards/rows: order number, placed date, item thumbs, total, and colored status pill (Pending/Confirmed/Processing/Shipped/Delivered/Rejected). "Details" -> links to order detail. Empty state when none.

### 3.3 Order Detail
> Design the E‑KHMER order detail page. Order number + status pill + placed date header. Product items with variant labels and line totals. Delivery address card. Payment method row. The 5‑stage tracking stepper. Totals summary (subtotal/discount/tax/shipping/total). "Download receipt" button.

### 3.4 Wishlist
> Design the E‑KHMER wishlist grid. Responsive product cards (same as storefront): heart filled red, price, "Add to Cart". Move‑to‑cart and remove affordances. Empty state with heart icon + "Browse products" CTA.

### 3.5 Addresses
> Design the E‑KHMER address book. Grid of address cards: label chip + "Default" green tag if default, full name, multi‑line address, phone; actions: Edit / Set Default / Delete. A dashed "＋ Add New Address" tile opens the address form (modal or page): Label, Full name, Address line 1, Line 2, City, State, Postal code, Country, Phone, "Save" primary + "Cancel". Optional "Set as default address" checkbox.

### 3.6 Profile
> Design the E‑KHMER profile page. Avatar upload circle (change photo), Name, Email (read‑only with verified badge or "Verify" button), Phone, and "Save changes" button.

### 3.7 Notifications
> Design the E‑KHMER notifications inbox. List of notification cards: icon/title, message, relative time, unread dot for new ones; "Mark all as read"; empty state with bell icon.

### 3.8 My Reviews
> Design the E‑KHMER "My Reviews" page. List of written reviews: product thumb + name, star rating, title, body, status pill (Approved/Pending/Rejected). Empty state.

### 3.9 Change Password
> Design the E‑KHMER change‑password form: Current password, New password (with strength meter), Confirm new password; validation errors inline, "Update password" button, success message.

---

## 4. Admin Screens ("ADMIN PANEL")

> **Shared shell (design once, reuse):** collapsible left sidebar 256px (collapses to 72px icon rail) with E‑KHMER "EK" logo + "ADMIN PANEL" tag. Grouped nav with small‑caps group labels: **Overview** (Dashboard), **Catalog** (Products, Add Product, Categories, Brands), **Fulfillment** (Orders, Inventory, Reports, Shipping Methods), **Marketing** (Coupons), **Community** (Customers, Reviews), **System** (Settings). Active item = blue tint. Collapse button at bottom.
> Top bar: menu/collapse toggle, page title, global search input (64 wide, hidden on mobile), language switch, theme toggle, bell icon with red unread dot opening a notifications dropdown (list + "View all"), and profile dropdown (avatar initial chip + name, menu: Profile, Settings, Sign out). Content area: page content on canvas `#F9FAFB`.
> **AdminDataTable spec** (reuse on Products/Orders/Customers/Coupons/Reviews/Shipping): toolbar (search input, bulk‑action dropdown, "Add/New" primary button top‑right) + table (checkboxes, sortable column headers with arrow icons, status/type/badge cells, right‑aligned "⋯" row action menu), footer with "Showing X–Y of Z" + pagination. Loading = skeleton rows. Empty = centered icon + message. Provide both light & dark variants.

### 4.1 Admin Dashboard
> Design the E‑KHMER admin dashboard.
> **4 KPI cards**: Total Revenue ($, large extrabold), Total Orders (count), Total Customers (count), and a danger ‑accent card "Low Stock Alerts" (red icon, red tint). Each w/ delta chip (+X% green / −X% red) + "vs last month".
> **Charts row**: Revenue Trend line/area chart (12 months, blue line) spanning 2 cols; Order Status donut chart with legend in the 3rd col.
> **Second row**: Sales by Category horizontal bar chart (1 col); Recent Orders table (2 col) — order, customer, total, status pill, date + "View all" link.
> **Low‑stock alert card**: title + description, list of low‑stock variants (product, variant, stock count in red) + "View all products" link; empty state "No low stock products".

### 4.2 Products (Admin)
> Design the E‑KHMER product management page: AdminDataTable with columns — Product (thumb + name + brand), Category, Price, Stock (sum of variants, red when low), Status (Active/Draft/Archived badge), Updated. "＋ Add Product" button; row actions: Edit / Duplicate / Delete (confirm modal).

### 4.3 Add / Edit Product
> Design the E‑KHMER product create/edit form (long, tabs or stacked sections):
> 1. **General**: Name, Slug, Brand (select), Category (select/tree), Short description, Full description (textarea).
> 2. **Pricing & Status**: Price, Compare‑at price, Tax class, Status toggle (Active/Draft), "Featured", "New" toggles.
> 3. **Images**: cover image upload + gallery upload grid with reorder/remove.
> 4. **Attributes & Variants**: attribute group manager (e.g. Color, Size) + a variants table (auto‑generated options, SKU, price, compare price, stock, image). Inline SKU + stock inputs.
> 5. **Inventory**: per‑variant quantity. Buttons: Cancel (outline) + "Save Product" (primary). Validation errors inline.

### 4.4 Orders (Admin)
> Design the E‑KHMER order management page: AdminDataTable with columns — Order number, Customer (name+email), Items count, Total, Status pill, Placed date, Actions. Filter by status chips. Row click → order detail.

### 4.5 Order Detail (Admin)
> Design the E‑KHMER admin order detail. Header: order number, status pill, placed date, customer name. Left: itemized product list (thumb, title, variant, unit price × qty, line total). Customer info card (contact + address). Payment card (method, amount, transactions). **Order processing actions box**: move status (Pending → Confirm → Process → Ship → Deliver) — button per allowed transition + optional notes input; timeline/method log. Right column or below: totals (subtotal, discount, tax, shipping, total).

### 4.6 Inventory
> Design the E‑KHMER inventory ledger page. **Cards/tiles**: Total SKUs, In stock, Low stock (red), Out of stock (amber). AdminDataTable: SKU, Variant (product + options), On hand, Reserved (for pending checkout), Available, Status badge, Last updated. Filter by stock status + search. Maybe "Export CSV" button.

### 4.7 Reports
> Design the E‑KHMER reports page: header w/ date‑range picker + "Export PDF" / "Export CSV" buttons. Chart cards: Revenue over time (line), Orders over time (line/bar), Sales by category (doughnut), Top products (bar or ranked list), New customers (line). Optional summary KPIs row on top.

### 4.8 Customers
> Design the E‑KHMER customers page: AdminDataTable — Customer (avatar + name + email), Orders count, Total spent, Joined date, Status badge (Active/Blocked), Actions (View / Block). Empty state.

### 4.9 Customer Detail
> Design the E‑KHMER customer detail: profile card (avatar, name, email, joined, status badge, order count, total spent) + actions (Block/Unblock). Tabs: **Orders** (recent orders table w/ status pills), **Addresses** (list), **Reviews** (list with status pills).

### 4.10 Reviews (Moderation)
> Design the E‑KHMER review moderation queue: AdminDataTable — product thumb + name, reviewer + avatar, star rating, review preview/excerpt, status pill (Pending/Approved/Rejected), date, actions. **Moderation bar**: filters by status; row action menu: Approve / Reject / Delete with confirm dialog. Empty states per filter.

### 4.11 Categories (Tree Manager)
> Design the E‑KHMER category manager: **tree view** of categories (expandable rows with indent + chevron), name, slug, product count, image thumb, visibility toggle, row actions (Edit / Add child / Delete). Toolbar w/ search + "＋ Add Category" (opens form: Name, Slug, Parent select, Image upload, Sort order). Drag‑to‑reorder affordance on the tree.

### 4.12 Brands
> Design the E‑KHMER brands page: grid or table of brands (logo/placeholder, name, slug, product count, active toggle) + "＋ Add Brand"; form = Name, Slug, Logo upload. Delete confirm modal.

### 4.13 Coupons
> Design the E‑KHMER coupons page: AdminDataTable — Code, Type badge (Percentage/Fixed), Value, Minimum order, Usage (used/limit), Expiry date, Status (Active/Expired). Row actions Edit / Delete. "＋ Add Coupon" form: Code, Type select (% or $), Value, Min order, Max discount, Usage limit, Starts / Expires date/time, Status toggle.

### 4.14 Shipping Methods
> Design the E‑KHMER shipping methods page: AdminDataTable or card list — name, description, price ("Free" or $), ETA range (min–max days), Status toggle. "＋ Add Shipping Method" form: Name, Description, Price, Min–max delivery days, Active toggle.

### 4.15 Settings
> Design the E‑KHMER admin settings page, tabbed: **Store** (store name, logo, currency, tax default, free‑shipping threshold), **Locales** (default language EN/KH, fallback), **Payments** (enable/disable COD, card, bank, gateway + keys placeholders), **Notifications** (admin email alerts toggles), **Security** (require email verification toggle, password length). Save bar sticky at bottom.

---

## 5. Auth Screens

> **Shared shell:** centered card (max ~420px) on a subtle gradient canvas; E‑KHMER logo on top, small tagline; card has 12px radius + shadow; inputs with inline validation; primary full‑width blue submit button; link row at bottom; theme + language toggles top‑right; dark variant.

### 5.1 Login
> Design the E‑KHMER login page. Title "Welcome back", subtitle. Email + Password fields (password has eye toggle). "Forgot password?" link right‑aligned. "Sign In" button. Bottom: "New to E‑KHMER? Create account". Validation errors under fields. Optional "Remember me" checkbox.

### 5.2 Register
> Design the E‑KHMER sign‑up page. Title "Create your account". Fields: Full name, Email, Password (strength meter), Confirm password, optional "I agree to terms & privacy" checkbox. "Create account" button. Bottom: "Already have an account? Sign in". After submit note: verification email sent.

### 5.3 Forgot Password
> Design the E‑KHMER forgot‑password page. Title "Reset your password", subtitle "Enter your email and we'll send you a reset link." Email field + "Send reset link" button. Success state: green check + "If an account exists, a reset link was sent." Back to Sign in link.

### 5.4 Reset Password (with token)
> Design the E‑KHMER reset‑password page. Title "Set a new password". New password (strength meter) + Confirm password. "Update password" button. Success card w/ check + "Sign in" CTA.

### 5.5 Verify Email
> Design the E‑KHMER email verification page. Mail icon, title "Verify your email", body "We've sent a verification link to you@email.com". "Resend verification email" button + countdown/cooldown state. Note about checking spam. After verified: green success state with "Continue" button.

---

## 6. Shared Component Spec (for consistency)

> Design the following reusable Figma components to match the tokens above:
> - `ProductCard` — 1:1 image, badges, wishlist button, brand/title/rating/price/color dots/+ Add to Cart (as in 2.2).
> - `ProductRail` — section header (eyebrow, title, subtitle, View all link) + horizontal scroll track with arrow buttons.
> - `StatusTag` — pill variants: pending (gray), confirmed (blue), processing (blue/amber), shipped (indigo), delivered (green), rejected (red), in‑stock (green), low‑stock (amber), out‑of‑stock (red). Small 10–12px rounded‑full chips with dot or icon.
> - `StarRating` — row of 5 stars (filled/partial/half star), may show numeric value.
> - `QuantityCounter` — compact [−] count [+].
> - `BaseModal` — centered dialog, title, body slot, footer, overlay backdrop close.
> - `EmptyState` — icon in gray, title, description, optional CTA button.
> - `BasePagination` — prev/next + page numbers + "Page X of Y" / "Showing A–B of C".
> - `BaseBadge`, `Chip`, `Skeleton` loading states, `Toast` notifications (success/error).
> - `DataTable` + `DataTableSkeleton` for admin.
> - `Chart cards` (Revenue/Orders/Category) using a clean chart styling: subtle grid lines, blue series color, amber/slate secondary.
> - Dark‑mode variants for every component (use midnight `#1E293B` surfaces, `#0B0F19` canvas, electric‑blue primary).