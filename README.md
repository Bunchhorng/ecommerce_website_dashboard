E-Commerce System

A full-stack E-Commerce Management System built with Laravel REST API, Vue 3 + TypeScript, MySQL, Redis, Nginx, and Docker.

📋 Project Overview

This project is designed as a complete e-commerce platform with two main parts:

Frontend — Vue 3 + TypeScript
Backend — Laravel REST API

The system supports product management, inventory, shopping cart, orders, payments, shipping, coupons, reviews, notifications, reports, and customer management.

🛠️ Technology Stack
Backend
Laravel
PHP 8.3
MySQL 8
Redis
Nginx
REST API
Laravel Sanctum
Frontend
Vue 3
TypeScript
Vite
Vue Router
Pinia
Axios
Bootstrap 5
SweetAlert2
Recharts / Chart.js
DevOps
Docker
Docker Compose
📁 Project Structure
ecommerce/
│
├── backend/
│   └── Laravel REST API
│
├── frontend/
│   └── Vue 3 + TypeScript
│
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   │
│   └── php/
│       └── Dockerfile
│
├── docker-compose.yml
├── .gitignore
└── README.md
🏗️ System Architecture
                         E-COMMERCE SYSTEM
                                │
              ┌─────────────────┴─────────────────┐
              │                                   │
              ▼                                   ▼
       ┌──────────────┐                    ┌──────────────┐
       │  Vue 3       │                    │   Laravel    │
       │  Frontend    │◄────── API ───────►│   Backend    │
       │  :5173       │                    │   :8000      │
       └──────────────┘                    └──────┬───────┘
                                                   │
                                  ┌────────────────┼────────────────┐
                                  │                │                │
                                  ▼                ▼                ▼
                             ┌─────────┐      ┌─────────┐      ┌─────────┐
                             │  MySQL  │      │  Redis  │      │ Storage │
                             │  :3306  │      │  :6379  │      │         │
                             └─────────┘      └─────────┘      └─────────┘
                                  │
                                  ▼
                             phpMyAdmin
                               :8080
🚀 Getting Started
1. Requirements

Make sure you have installed:

Docker Desktop
Git
VS Code or another code editor

Check Docker:

docker --version

Check Docker Compose:

docker compose version
2. Clone the Project
git clone <repository-url>

Go into the project:

cd ecommerce

Expected structure:

ecommerce/
├── backend/
├── frontend/
├── docker/
├── docker-compose.yml
└── README.md
3. Configure Laravel

Go to the backend:

cd backend

Create .env:

cp .env.example .env

For Windows PowerShell, you can use:

Copy-Item .env.example .env

Configure the database:

APP_NAME=Ecommerce
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=ecommerce_db
DB_USERNAME=ecommerce_user
DB_PASSWORD=ecommerce_password

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
Important

Because Laravel is running inside Docker, use:

DB_HOST=mysql

Do not use:

DB_HOST=localhost
4. Configure Vue

Go to:

frontend/

Create:

frontend/.env

Add:

VITE_API_URL=http://localhost:8000/api

The Vue frontend will use this URL to communicate with Laravel.

🐳 Docker Setup
5. Build Docker Containers

From the project root:

docker compose up -d --build

This starts:

Laravel
Vue
Nginx
MySQL
Redis
phpMyAdmin

Check containers:

docker compose ps
6. Install Laravel Dependencies
docker compose exec app composer install

Generate Laravel application key:

docker compose exec app php artisan key:generate
7. Run Laravel Migration
docker compose exec app php artisan migrate

If you have seed data:

docker compose exec app php artisan migrate --seed

Or reset and seed the database:

docker compose exec app php artisan migrate:fresh --seed

⚠️ migrate:fresh deletes all existing database tables and data.

8. Create Laravel Storage Link
docker compose exec app php artisan storage:link

This is required for uploaded files such as:

Product images
Brand logos
Category images
Review images
User profile images
9. Install Vue Dependencies
docker compose exec frontend npm install
🌐 Application URLs

After Docker starts successfully:

Frontend
http://localhost:5173
Laravel API
http://localhost:8000
API Base URL
http://localhost:8000/api
phpMyAdmin
http://localhost:8080
🗄️ Database Configuration
Docker Internal Connection

Laravel → MySQL:

Host: mysql
Port: 3306
Database: ecommerce_db
Username: ecommerce_user
Password: ecommerce_password
Host Machine Connection

If connecting from MySQL Workbench or another database tool:

Host: localhost
Port: 3307
Database: ecommerce_db
Username: ecommerce_user
Password: ecommerce_password

The reason for the different ports is:

Your Computer
     │
     │ localhost:3307
     ▼
Docker MySQL
     │
     │ 3306
     ▼
MySQL Server
🔐 phpMyAdmin

Open:

http://localhost:8080

Use:

Server: mysql
Username: ecommerce_user
Password: ecommerce_password

Or:

Server: mysql
Username: root
Password: root_password
📦 E-Commerce Features
Customer Website
Home
Shop
Product Details
Categories
Brands
Search
Product Filtering
Product Sorting
Shopping Cart
Wishlist
Checkout
Address Management
Shipping
Payment
Order Tracking
Reviews
Notifications
Customer Profile
Admin Dashboard
Dashboard
│
├── Products
├── Categories
├── Brands
├── Inventory
├── Orders
├── Payments
├── Shipping
├── Customers
├── Coupons
├── Reviews
├── Notifications
├── Reports
└── Settings
📦 Product Management

The product system supports:

Products
│
├── Category
├── Brand
├── Product Images
├── Product Variants
├── Attributes
├── Attribute Values
├── Variant Attribute Values
└── Inventory

Example:

T-Shirt
│
├── Color
│   ├── Black
│   ├── White
│   └── Blue
│
└── Size
    ├── S
    ├── M
    ├── L
    └── XL
📦 Inventory Management

Inventory supports:

Inventory
│
├── Stock Quantity
├── Reserved Quantity
├── Available Quantity
├── Stock Adjustment
├── Stock In
├── Stock Out
└── Inventory Transactions
🛒 Shopping Cart
Customer
   │
   ▼
Product
   │
   ▼
Select Variant
   │
   ▼
Add To Cart
   │
   ▼
Cart
   │
   ├── Update Quantity
   ├── Remove Item
   └── Apply Coupon
📦 Order Flow
Cart
 │
 ▼
Checkout
 │
 ▼
Address
 │
 ▼
Shipping Method
 │
 ▼
Coupon
 │
 ▼
Payment
 │
 ▼
Create Order
 │
 ▼
Order Confirmation
 │
 ▼
Shipment
 │
 ▼
Order Delivered
💳 Payment Flow
Checkout
   │
   ▼
Payment Method
   │
   ▼
Payment Transaction
   │
   ├── Pending
   ├── Paid
   ├── Failed
   └── Refunded
🚚 Shipping Flow
Order
 │
 ▼
Shipment Created
 │
 ▼
Processing
 │
 ▼
Shipped
 │
 ▼
In Transit
 │
 ▼
Delivered
⭐ Review Flow
Customer
   │
   ▼
Completed Order
   │
   ▼
Review Product
   │
   ├── Rating
   ├── Comment
   └── Images

Admin can:

View Review
Approve Review
Reject Review
Delete Review
📊 Reports

The admin dashboard provides:

Sales Reports
Order Reports
Product Reports
Customer Reports
Inventory Reports
Payment Reports
Shipping Reports

Example dashboard:

┌───────────────┬───────────────┬───────────────┐
│ Total Sales   │ Total Orders  │ Customers     │
│ $25,500       │ 1,250         │ 5,430         │
└───────────────┴───────────────┴───────────────┘

             Sales Chart
        ╭────────────────╮
        │       ╱╲       │
        │    ╱╱   ╲╲     │
        │ ╱╱         ╲   │
        └─────────────────┘
🔔 Notifications

Notifications can be generated for:

New Order
Payment Success
Payment Failed
Order Shipped
Order Delivered
Coupon
Product Stock Low
Product Out of Stock
New Review
⚙️ Settings

The system includes:

General Settings
Store Settings
Payment Settings
Shipping Settings
Notification Settings
Security Settings
User & Role Settings
System Settings
Activity Logs
🐳 Useful Docker Commands
Start
docker compose up -d
Build and Start
docker compose up -d --build
Stop
docker compose stop
Stop and Remove Containers
docker compose down
View Containers
docker compose ps
View Logs
docker compose logs -f
Laravel Logs
docker compose logs -f app
Nginx Logs
docker compose logs -f nginx
Vue Logs
docker compose logs -f frontend
MySQL Logs
docker compose logs -f mysql
Redis Logs
docker compose logs -f redis
🔧 Laravel Commands

Run Artisan:

docker compose exec app php artisan

Clear Laravel cache:

docker compose exec app php artisan optimize:clear

Show routes:

docker compose exec app php artisan route:list

Create controller:

docker compose exec app php artisan make:controller ProductController

Create model:

docker compose exec app php artisan make:model Product -m

Run migration:

docker compose exec app php artisan migrate
📦 Composer Commands

Install dependencies:

docker compose exec app composer install

Update dependencies:

docker compose exec app composer update

Install a package:

docker compose exec app composer require package-name
🟢 Vue Commands

Install dependencies:

docker compose exec frontend npm install

Run development server:

docker compose exec frontend npm run dev

Build frontend:

docker compose exec frontend npm run build

Check packages:

docker compose exec frontend npm list
🔴 Redis Test

Enter Redis:

docker compose exec redis redis-cli

Test connection:

PING

Expected:

PONG

Exit:

exit
🗃️ MySQL Test

Enter MySQL:

docker compose exec mysql mysql -u ecommerce_user -p ecommerce_db

Show tables:

SHOW TABLES;

Exit:

exit;
🧹 Reset Database

To reset only Laravel database tables:

docker compose exec app php artisan migrate:fresh --seed

To completely remove Docker volumes:

docker compose down -v

Then rebuild:

docker compose up -d --build

⚠️ docker compose down -v deletes the MySQL Docker volume and therefore all database data stored in it.

#insatll laravel project for backend
docker compose exec app composer create-project laravel/laravel .

#install vue project for frontend
docker run --rm -it -v "C:/All_Document/My_Project/E-Ecommerce/frontend:/app" -w //app node:22-alpine npm create vite@latest . -- --template vue-ts

🔄 Development Workflow

Typical daily workflow:

# Start project
docker compose up -d

Then:

Frontend
http://localhost:5173

Backend
http://localhost:8000

phpMyAdmin
http://localhost:8080

When finished:

docker compose down
🔗 API Architecture
Vue 3
  │
  │ Axios
  ▼
Laravel REST API
  │
  ├── Authentication
  ├── Products
  ├── Categories
  ├── Brands
  ├── Inventory
  ├── Cart
  ├── Wishlist
  ├── Orders
  ├── Payments
  ├── Shipping
  ├── Coupons
  ├── Reviews
  ├── Notifications
  ├── Reports
  └── Settings
  │
  ├───────────────┐
  ▼               ▼
MySQL           Redis
🔒 Security

Do not commit sensitive environment files.

Make sure .gitignore contains:

.env
backend/.env
frontend/.env
/vendor/
/node_modules/
/storage/*.key

Never commit:

Database passwords
API keys
Payment secret keys
Laravel APP_KEY
JWT secrets
Production credentials

For production, use secure environment variables and HTTPS.

🌍 Production

The development configuration is intended for local development.

For production, use:

Internet
   │
   ▼
HTTPS
   │
   ▼
Nginx
   │
   ▼
Laravel
   │
   ├── MySQL
   ├── Redis
   └── Queue Workers

The Vue application should be built with:

npm run build

and served as static files through a production web server or CDN.

👨‍💻 Development Team Workflow

Recommended Git workflow:

main
 │
 ├── develop
 │    │
 │    ├── feature/products
 │    ├── feature/orders
 │    ├── feature/inventory
 │    ├── feature/payments
 │    ├── feature/shipping
 │    └── feature/reviews
 │
 └── release

Example:

git checkout -b feature/products

After completing the feature:

git add .
git commit -m "feat: add product management"
git push origin feature/products
📌 Quick Start

For developers who already have Docker installed:

git clone <repository-url>

cd ecommerce

docker compose up -d --build

docker compose exec app composer install

docker compose exec app php artisan key:generate

docker compose exec app php artisan migrate --seed

docker compose exec app php artisan storage:link

docker compose exec frontend npm install

Open:

Vue:
http://localhost:5173

Laravel:
http://localhost:8000

phpMyAdmin:
http://localhost:8080

License

This project is developed for educational and academic purposes.

E-Commerce System
Laravel + Vue 3 + MySQL + Redis + Docker