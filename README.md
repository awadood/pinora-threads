# Pinora Threads — Platform API

Pinora Threads is a full-featured e-commerce platform for traditional Pakistani and Indian fashion. The API powers every aspect of the shopping experience — product catalog, inventory, cart, orders, payments, shipping, promotions, customer accounts, and storefront merchandising — for customers in Pakistan, the United States, and beyond.

---

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Authentication & Security](#authentication--security)
- [Roles & Permissions](#roles--permissions)
- [Store Context](#store-context)
- [Modules](#modules)
  - [Product Catalog](#product-catalog)
  - [Inventory Management](#inventory-management)
  - [Shopping Cart](#shopping-cart)
  - [Orders](#orders)
  - [Payments](#payments)
  - [Shipping & Fulfillment](#shipping--fulfillment)
  - [Promotions & Coupons](#promotions--coupons)
  - [Customer Accounts](#customer-accounts)
  - [Wishlists](#wishlists)
  - [Tax Engine](#tax-engine)
  - [Media Management](#media-management)
  - [Engagement & Merchandising](#engagement--merchandising)
  - [User & Admin Management](#user--admin-management)
  - [Activity Logging](#activity-logging)
- [Background Jobs](#background-jobs)
- [Getting Started](#getting-started)
- [Environment Variables](#environment-variables)

---

## Overview

The Pinora Threads API is built on **Laravel 13** (PHP 8.3+) with **PostgreSQL** as the primary database. It serves both the customer-facing storefront and an administrative back-office. The API is organized into clearly separated domain layers — Controllers, Services, Repositories, and Models — to keep business logic clean and maintainable as the catalog and order volume grows.

---

## Architecture

- **Framework**: Laravel 13 (PHP 8.3+)
- **Database**: PostgreSQL
- **Authentication**: Laravel Sanctum (token-based for API clients, cookie-based for admin SPA)
- **Authorization**: Spatie Laravel Permission (RBAC)
- **Audit Logging**: Spatie Laravel Activity Log
- **Media Storage**: AWS S3 with multiple rendition profiles per image
- **Image Processing**: Intervention Image
- **Queue**: Database-backed queue for background jobs
- **Domain Layer**: Controllers → Services → Repositories → Models

---

## Authentication & Security

**Two authentication modes are supported:**

**Token-based (API clients):**
1. `POST /api/login` — authenticate with email and password, receive an API token
2. Include the token as `Authorization: Bearer <token>` on subsequent requests

**Cookie-based (admin SPA):**
1. `GET /sanctum/csrf-cookie` — obtain a CSRF cookie
2. `POST /api/auth/login` — authenticate; session cookie is set
3. All subsequent SPA requests carry the session cookie and CSRF header automatically

**Security features:**
- CSRF protection on all state-mutating requests
- Domain-restricted stateful sessions
- Idempotency key support on order creation and payment routes — duplicate submissions are detected and short-circuited
- Rate limiting on authentication routes
- Role-based access control enforced at the route level

---

## Roles & Permissions

Permissions are centralized and enforced per route. All permission constants live in `app/Support/Permissions.php`.

| Role | Description |
|---|---|
| **Super Admin** | Unrestricted access to all platform capabilities |
| **Admin** | Full access to catalog, inventory, orders, customers, and promotions |
| **Catalog Manager** | Manage products, categories, collections, attributes, and media |
| **Inventory Manager** | Manage stock levels, batches, and movements |
| **Order Manager** | View and process orders, shipments, and refunds |
| **Customer** | Authenticated shopper — access to personal orders, addresses, and wishlists |

---

## Store Context

The platform supports serving customers across multiple countries and currencies simultaneously. A store context is resolved on every request and carries:

- **Country** — the customer's market (default: Pakistan / PK)
- **Currency** — the active currency for pricing and totals (default: PKR)
- **Available currencies** — list of all currencies the store accepts

The store context is resolved via `GET /api/store-context` and is embedded in every API response so the frontend always knows which market it is operating in.

---

## Modules

### Product Catalog

The catalog is the core of Pinora Threads, supporting the full complexity of traditional fashion merchandise.

**Products:**
- Product records with name, SKU, description, and tax class
- Support for simple products, variant products, and product bundles
- Multi-currency pricing — each product can have independent prices per currency
- SEO metadata (title, description, og:image) per product

**Variants:**
- Each product supports unlimited variants combining any attributes (color, size, fabric, occasion)
- Each variant has its own SKU, stock level, and pricing
- Variant availability surfaced in real time during browsing

**Attributes:**
- Configurable attribute types (color, size, fabric, occasion, etc.)
- Attribute options with display values
- Filterable and searchable attributes for the storefront PLP

**Taxonomy:**
- Categories with slug-based routing and SEO metadata
- Collections for editorial groupings (seasonal, occasion-based, fabric-based)
- Products can belong to multiple categories and collections
- Related products for cross-sell recommendations

**Bundles:**
- Group multiple products into a purchasable bundle with combined pricing

---

### Inventory Management

A full inventory system tracks every unit of stock across its entire lifecycle.

- **Stock levels** per variant — real-time quantity available for purchase
- **Stock batches** — group received inventory with received date and supplier reference
- **Stock movements** — immutable ledger of every inventory change (purchase receipt, sale, return, damage write-off, manual adjustment)
- **Stock movement types** — configurable types for classifying movements
- **Back-in-stock subscriptions** — customers subscribe to out-of-stock variants and receive an email notification the moment stock is replenished
- Automatic stock level recomputation when batches are received or adjustments are posted

---

### Shopping Cart

The cart supports both guest shoppers and authenticated customers with seamless continuity.

- Add, update quantity, and remove line items
- Guest carts are persisted client-side and merged into the customer's cart automatically on login
- Apply and remove promotion coupon codes with real-time discount calculation
- Select a shipping method with live rate display
- Cart expiration — idle carts are cleaned up by a scheduled job
- Guest cart and authenticated cart share the same API surface

---

### Orders

The order engine handles the full order lifecycle from placement to completion.

- Order creation from a confirmed cart (checkout)
- Guest order placement with a secure claim link — guests can later claim an order into their account without losing order history
- Guest order tracking via email and order reference — no account required
- Order status workflow: pending → confirmed → processing → shipped → delivered → completed
- Order cancellation with reason tracking
- Authenticated customers view and manage their full order history
- Admins view, filter, and update the status of all orders
- Idempotency keys prevent duplicate order creation on network retries
- Automatic cancellation of unpaid orders older than 30 minutes

---

### Payments

A complete payment engine handles all financial transactions and reconciliation.

- Payment methods configurable by admin (Cash on Delivery, credit card, etc.)
- Payment attempts tracked individually — retries and failures are recorded
- Invoice generation per order with status workflow (draft → issued → paid → void)
- Full and partial refund processing with status tracking
- COD payment collection marked by admin once cash is received
- Payment attempt and refund history per order for full financial auditability

---

### Shipping & Fulfillment

The shipping module manages fulfillment from dispatch to delivery.

- Configurable shipment methods (carriers, rates, delivery windows)
- Shipment records created per order with tracking number and carrier details
- Shipment status workflow: pending → dispatched → in-transit → delivered
- Customers track their shipment status from their order detail page
- Admins create, update, and manage all shipments

---

### Promotions & Coupons

A flexible promotions engine drives sales and customer retention.

- Promotion definitions with start/end dates and discount type (percentage or fixed amount)
- Promotion status lifecycle: upcoming → ongoing → paused → completed (updated automatically by scheduled job)
- Coupon codes tied to promotions for redemption at checkout
- Redemption tracking — records which customer redeemed which coupon and for how much
- Redemption analytics available to admins

---

### Customer Accounts

Every customer has a full account with purchase history and personal preferences.

- Customer profile with name, contact details, and preferences
- Multiple saved delivery addresses with a default address selection
- Full order history with status and tracking
- Guest order claiming — orders placed without an account are linked to the customer's profile after login
- Recently viewed products tracked per session and per account

---

### Wishlists

Customers maintain private wishlists to save and organize products they love.

- Create and name multiple wishlists
- Add and remove products across wishlists
- Shareable wishlists via a secure token link — share a wishlist without requiring the recipient to log in
- Wishlist items carry the variant-level detail (color, size) so the exact item is saved

---

### Tax Engine

A rules-based tax engine handles multi-jurisdiction tax scenarios.

- **Tax classes** — classify products by their tax treatment (standard, reduced, exempt)
- **Tax rates** — define the applicable rate per jurisdiction and class
- **Tax rules** — map tax rates to product tax classes and customer groups
- **Tax calculations** — computed at checkout and stored per order for reporting and compliance
- GST and VAT supported independently

---

### Media Management

A dedicated media system handles all product and editorial imagery at scale.

- Media assets stored on AWS S3
- Multiple rendition profiles per image (thumbnail, PLP card, hero, OG image, gallery)
- Renditions generated automatically when assets are uploaded
- Presigned S3 upload URLs — clients upload directly to S3 without routing through the API server
- Media attachments linked to products, variants, and lookbook items
- Attachment ordering and primary image designation
- Video metadata support for product and editorial video content

---

### Engagement & Merchandising

Content and editorial tools that power the storefront experience.

**Testimonials:**
- Customer testimonial records with name, text, and rating
- Displayed on the storefront homepage and product pages

**Lookbooks:**
- Editorial collections of curated images paired with shoppable product links
- Each lookbook contains items (editorial images) and each item links to one or more products
- Admins create and manage lookbooks through the admin interface

**Merch Sections:**
- Admin-configurable homepage sections for featuring products and collections
- Section type, title, and item list all managed via API
- Storefront fetches active merch sections to build the homepage dynamically

---

### User & Admin Management

Platform users (admins and staff) are managed independently from customer accounts.

- Admin user CRUD with role assignment
- Role and permission management — define custom roles with specific permission sets
- Sync roles and permissions on existing users
- Toggle user active/inactive status
- Email verification flow for new admin accounts
- Password reset via email link

---

### Activity Logging

Every admin action in the system is captured in a full audit log.

- Records the acting user, the affected model and record, and the event type
- Before and after state captured on every mutation
- Filterable activity log available in the admin interface
- Provides a complete, tamper-evident history for compliance and dispute resolution

---

## Background Jobs

Scheduled jobs run automatically to keep the platform's data accurate and timely.

| Job | Schedule | Description |
|---|---|---|
| Cancel unpaid orders | Every minute | Cancels orders that have been unpaid for more than 30 minutes |
| Expire carts | Hourly | Removes carts past their expiration timestamp |
| Update promotion statuses | Every 15 minutes | Flips promotions between upcoming, ongoing, paused, and completed states based on their configured dates |
| Back-in-stock notifications | On stock event | Emails subscribed customers when a variant they were watching comes back into stock |

---

## Getting Started

### Requirements

- PHP 8.3+
- PostgreSQL 14+
- Composer 2+
- AWS S3 bucket (or compatible object storage) for media

### Installation

```bash
# Clone the repository
git clone <repository-url> threads
cd threads

# Install PHP dependencies
composer install

# Copy environment file and configure
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed reference data
php artisan db:seed

# Start the development server (with queue worker and logs)
composer run dev
```

---

## Environment Variables

| Variable | Description |
|---|---|
| `APP_NAME` | Application name |
| `APP_URL` | Backend base URL (e.g., `http://localhost:8000`) |
| `DB_CONNECTION` | Database driver (`pgsql`) |
| `DB_HOST` | PostgreSQL host |
| `DB_PORT` | PostgreSQL port (default: `5432`) |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `AWS_ACCESS_KEY_ID` | AWS credentials for S3 media storage |
| `AWS_SECRET_ACCESS_KEY` | AWS credentials for S3 media storage |
| `AWS_DEFAULT_REGION` | AWS region (e.g., `us-east-1`) |
| `AWS_BUCKET` | S3 bucket name for media assets |
| `QUEUE_CONNECTION` | Queue driver (default: `database`) |
| `MAIL_MAILER` | Mail driver for transactional emails |
| `STOREFRONT_DEFAULT_COUNTRY` | Default store country code (e.g., `PK`) |
| `STOREFRONT_DEFAULT_CURRENCY` | Default store currency code (e.g., `PKR`) |
| `STOREFRONT_CLAIM_LINK_TTL` | Minutes a guest order claim link stays valid (default: `1440`) |
| `SANCTUM_STATEFUL_DOMAINS` | Comma-separated trusted frontend domains |
| `SESSION_DOMAIN` | Cookie session domain |
