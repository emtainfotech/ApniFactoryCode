# 🏭 ApniFactory — B2B Multi-Vendor Manufacturing & Marketplace Engine

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-9.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 9.x" />
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/Filament-Admin%20v2-F59E0B?style=for-the-badge&logo=livewire&logoColor=white" alt="Filament Admin" />
  <img src="https://img.shields.io/badge/Architecture-REST%20API%20%2B%20Web%20Portal-10B981?style=for-the-badge" alt="REST API" />
  <img src="https://img.shields.io/badge/License-Proprietary-blue?style=for-the-badge" alt="License" />
</p>

---

## 📖 Overview

**ApniFactory** is a high-performance B2B multi-vendor manufacturing marketplace platform connecting industrial manufacturers, mills, distributors, and buyers. The platform features an intelligent **Two-Price Matrix Engine** (Factory Price vs Customer Marketplace Price), automated order fulfillment workflows with strict **3-Day Seller SLA timers**, automated **buyer refund & alternative seller recommendations**, and a comprehensive **Filament Admin Control Suite** with complete audit history and change logging.

---

## 🌟 Key Features & Core Engines

### 1. ⚙️ Universal Smart Pricing & Two-Price Matrix Engine
- **Factory Price vs Marketplace Price**: Sellers input their raw factory price; the system automatically calculates and applies the platform commission/markup (e.g. 25%).
- **Bulk Price Adjustments**:
  - **₹ / Litre**: Adjusts price per volume based on pack litrage (1L, 4L, 10L, 20L).
  - **Percentage (%)**: Proportional price shifts across all selected SKUs.
  - **Fixed (₹)**: Flat rupee increase/decrease across target variants.
- **Granular Scope Control**: Apply price shifts by **Entire Category**, **Product Family**, **Specific Shades / Colors**, or **Specific Packaging Sizes**.
- **Real-Time Calculation & Atomic DB Application**: Instant client-side preview with color indicators, delta badges, and atomic database transaction commits.

### 2. ⏱️ 3-Day Seller SLA & Auto-Cancellation Engine
- **Live Visual Countdown**: Real-time ticker in the seller order dashboard (`🟢 Safe`, `🟡 Action Required`, `🔴 Expired`).
- **Automated Cancellation Cron**: An automated command (`php artisan app:cancel-expired-orders`) that scans pending orders past the 3-day deadline, cancels them automatically, updates seller performance metrics, marks payments as refunded, and notifies the buyer.

### 3. 🔄 Alternative Sellers Recommendation Algorithm
- When an order is rejected or auto-cancelled, the system automatically identifies the **top 3 alternative active sellers** providing the same product category.
- Orders alternatives based on minimum order value, proximity, and low rejection rates, exposing clean REST payloads for mobile and web clients (`/api/customer/orders/{id}/alternative-sellers`).

### 4. 🔔 Multi-Channel Real-Time Notification System
- **Seller Real-Time Center**: Low-latency AJAX notification polling (15s interval) in the seller topbar with unread count badges.
- **Login Modal**: Alerts sellers immediately upon login if there are pending unread orders requiring acceptance.
- **Buyer & Admin Notifications**: Multi-channel router supporting Database Notifications, Firebase Cloud Messaging (FCM Push), WhatsApp Cloud API, and Transactional Email.

### 5. 📊 Company Change Logs & Audit History in Filament Admin
- **Pricing History & Adjustments RelationManager**: Full historical audit trail of all price adjustments with an interactive **SKU Breakdown Modal** displaying before/after factory and customer prices.
- **Company Activity Logs RelationManager**: Detailed change history for Minimum Order Values (MOV), password changes, store banners, and logos with a **Payload Comparison Modal**.
- **Color-Coded Status Spectrum**: Status badges in the Filament admin panel across all lifecycle stages (`🟡 Pending Seller`, `🟢 Accepted`, `🔵 Processing`, `🟠 In Transit`, `🟢 Delivered`, `🔴 Rejected`, `⚫ Cancelled`).

---

## 🏗️ Project Architecture & Directory Structure

```
ApniFactoryBackend/
├── app/
│   ├── Console/Commands/
│   │   └── CancelExpiredOrders.php            # Auto-cancellation scheduler command
│   ├── Filament/Resources/                    # Filament v2 Admin Control Panel
│   │   ├── CompanyResource.php                # Company management with RelationManagers
│   │   ├── CompanyResource/RelationManagers/
│   │   │   ├── PriceAdjustmentsRelationManager.php # Pricing history & SKU breakdown modal
│   │   │   └── AuditLogsRelationManager.php        # Company change logs & payload modal
│   │   ├── OrderResource.php                  # Color-coded order management
│   │   └── ProductResource.php                # Multi-category product management
│   ├── Http/Controllers/
│   │   ├── PaintPricingController.php         # Universal Smart Pricing Manager
│   │   ├── NotificationController.php         # Real-time notification endpoints
│   │   ├── CompanyController.php              # Seller company profile & audit hooks
│   │   ├── OrderController.php                # Order lifecycle, tax, & PDF invoices
│   │   └── CustomerController.php             # Bcrypt-hashed customer auth & profiles
│   ├── Models/
│   │   ├── CompanyAuditLog.php                # Change tracking model
│   │   ├── PaintPriceAdjustment.php           # Pricing history snapshot model
│   │   ├── ProductAttributes.php              # Multi-tier variant & SKU model
│   │   └── Order.php                          # Order entity with SLA timestamps
│   └── Services/
│       ├── AlternativeSellerService.php       # Top 3 alternative seller finder
│       ├── PaintPricingService.php            # Two-price matrix & bulk pricing logic
│       └── NotificationService.php            # Multi-channel notification dispatcher
├── database/
│   ├── migrations/                            # Schema definitions & pre-launch columns
│   └── seeders/
│       └── DemoMultiCategoryProductSeeder.php # Seeds 12 multi-category demo products
├── resources/views/
│   ├── filament/components/                   # Modal views for SKU breakdowns & diffs
│   ├── product/paint_pricing.blade.php        # Smart Pricing Manager UI & scripts
│   ├── order/sellerorderlist.blade.php        # 3-Day timer & rejection modals
│   └── sidebar.blade.php                      # Topbar notification polling & tickets
└── routes/
    ├── web.php                                # Seller portal & Web routes
    └── api.php                                # Mobile App REST API endpoints
```

---

## 🚀 Getting Started & Local Setup

### 1. Prerequisites
- **PHP**: `^8.1` or `^8.2`
- **Database**: MySQL `5.7+` or MariaDB `10.4+`
- **Composer**: `v2.x`
- **Node.js**: `v16+` / `npm`

### 2. Installation Steps

1. **Clone the repository**:
   ```bash
   git clone https://github.com/emtainfotech/ApniFactoryCode.git
   cd ApniFactoryCode
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment File**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) in `.env`.*

4. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=DemoMultiCategoryProductSeeder
   ```

5. **Symlink Storage Directory**:
   ```bash
   php artisan storage:link
   ```

6. **Start Local Development Server**:
   ```bash
   php artisan serve --port=8000
   ```

---

## 🔑 Default Credentials

| Portal | URL | Username / Email | Password |
| :--- | :--- | :--- | :--- |
| **Filament Admin** | `http://127.0.0.1:8000/admin` | `admin@admin.com` (or superadmin) | `password` / `admin123` |
| **Seller Portal** | `http://127.0.0.1:8000/seller/login` | `seller@apnifactory.local` | `password123` |
| **Smart Pricing** | `http://127.0.0.1:8000/seller/paint-pricing` | *(Logged in as Seller)* | — |

---

## 📡 Key REST API Endpoints (For Mobile Apps)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/login` | Customer login (Bcrypt verified, returns user & token). |
| `POST` | `/api/register` | Customer registration with auto-hashed password. |
| `GET` | `/api/homescreen` | Home screen banners, top categories, and featured products. |
| `POST` | `/api/addtocart` | Adds product variant with packaging unit & color shade. |
| `POST` | `/api/order/create` | Order creation with MOV validation and intra/inter-state GST. |
| `GET` | `/api/customer/orders/{id}/alternative-sellers` | Returns top 3 alternative sellers if order was rejected. |
| `GET` | `/api/customer/notifications` | Returns customer notification history. |
| `POST` | `/api/customer/update-fcm-token` | Updates mobile device FCM token for push notifications. |
| `GET` | `/api/seller/paint-families/{id}/pricing` | Matrix of all SKUs with Factory vs Customer prices. |

---

## 📚 Detailed Documentation & Audit References

- 📋 [**CHANGES_IMPLEMENTED_IN_DETAIL.md**](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/CHANGES_IMPLEMENTED_IN_DETAIL.md): Complete technical changelog of all implemented features, files modified, and bug fixes.
- 📋 [**PENDING_AND_UNFULFILLED_REQUIREMENTS.md**](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/PENDING_AND_UNFULFILLED_REQUIREMENTS.md): External production credentials, server crontabs, and mobile app frontend integration guide.
- 📋 [**COMPREHENSIVE_CODE_AUDIT_AND_ROADMAP.md**](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/COMPREHENSIVE_CODE_AUDIT_AND_ROADMAP.md): Full codebase security and architecture audit.
- 📋 [**PROJECT_DOCUMENTATION.md**](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/PROJECT_DOCUMENTATION.md): Deep-dive domain architecture and business workflows.
