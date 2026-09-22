# 🗄️ ApniFactory: Complete Database & Code Changes Report

> **Comprehensive Reference of All Database Migrations, Schema Modifications, Models, Seeders, and Codebase Changes Implemented.**

---

## 📑 Table of Contents

1. [Database Schema Modifications & Migrations](#1-database-schema-modifications--migrations)
   - [A. New Tables Created](#a-new-tables-created)
   - [B. Existing Tables Modified (New Columns)](#b-existing-tables-modified-new-columns)
   - [C. Database Seeders Created](#c-database-seeders-created)
2. [Eloquent Models & Relationships Added](#2-eloquent-models--relationships-added)
3. [Full Codebase Changes & Systems Implemented](#3-full-codebase-changes--systems-implemented)
   - [1. Universal Smart Pricing & Two-Price Matrix Engine](#1-universal-smart-pricing--two-price-matrix-engine)
   - [2. 3-Day Seller SLA & Auto-Cancellation Cron](#2-3-day-seller-sla--auto-cancellation-cron)
   - [3. Alternative Sellers Recommendation System](#3-alternative-sellers-recommendation-system)
   - [4. Multi-Channel Notification Router](#4-multi-channel-notification-router)
   - [5. Automated Refund & Webhook Manager](#5-automated-refund--webhook-manager)
   - [6. Filament Admin Control Suite with Modals](#6-filament-admin-control-suite-with-modals)
   - [7. Customer Mobile Experience Simulator](#7-customer-mobile-experience-simulator)
4. [Bug Fixes & Security Hardening Log](#4-bug-fixes--security-hardening-log)
5. [Summary of Modified & Created Files](#5-summary-of-modified--created-files)

---

## 1. Database Schema Modifications & Migrations

### A. New Tables Created

#### 1. `company_audit_logs` Table
- **Migration File**: [`database/migrations/2026_08_24_101313_create_company_audit_logs_table.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/database/migrations/2026_08_24_101313_create_company_audit_logs_table.php)
- **Purpose**: Tracks every change made to a company profile, Minimum Order Value (MOV), passwords, logos, banners, commission rates, and pricing adjustments.
- **SQL / Schema Definition**:
```php
Schema::create('company_audit_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable()->index();
    $table->unsignedBigInteger('user_id')->nullable()->index();
    $table->string('actor_name')->nullable();
    $table->string('actor_role')->default('seller'); // seller, admin, system
    $table->string('action_type')->index();          // price_adjustment, min_order_value_change, profile_update, banner_update, password_update
    $table->string('title');
    $table->text('description')->nullable();
    $table->json('old_values')->nullable();          // JSON snapshot of previous values
    $table->json('new_values')->nullable();          // JSON snapshot of updated values
    $table->string('ip_address')->nullable();
    $table->timestamps();
});
```

---

#### 2. `paint_price_adjustments` Table
- **Migration File**: [`database/migrations/2026_01_02_000002_create_paint_price_adjustments_table.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/database/migrations/2026_01_02_000002_create_paint_price_adjustments_table.php)
- **Purpose**: Persists immutable audit logs of bulk pricing changes executed across Category, Product Family, Shades, or Pack Sizes.
- **SQL / Schema Definition**:
```php
Schema::create('paint_price_adjustments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->unsignedBigInteger('product_id')->nullable();
    $table->string('adjustment_type');                // per_litre, percentage, fixed
    $table->decimal('adjustment_value', 10, 2);
    $table->string('scope_type')->default('family');  // category, family, shades, packings, skus
    $table->json('scope_json')->nullable();           // JSON of selected category_id, shade_ids, packing_ids
    $table->integer('affected_count')->default(0);    // Number of SKUs updated
    $table->longText('preview_data')->nullable();     // Full JSON matrix snapshot with old/new prices
    $table->unsignedBigInteger('created_by')->nullable();
    $table->timestamps();
});
```

---

### B. Existing Tables Modified (New Columns)

#### 1. `product_attributes` Table
- **Migration File**: [`database/migrations/2026_01_02_000001_add_seller_pricing_to_product_attributes_table.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/database/migrations/2026_01_02_000001_add_seller_pricing_to_product_attributes_table.php)
- **Columns Added**:
  - `seller_price` (`DECIMAL(12,2)`, nullable): Factory Base Price set by manufacturer.
  - `commission_rate` (`DECIMAL(5,2)`, default `25.00`): Platform commission percentage markup.
  - `pack_litres` (`DECIMAL(8,2)`, default `1.00`): Precise numeric litrage / volume for calculating per-litre rate shifts.

---

#### 2. `orders` Table
- **Columns Added**:
  - `seller_deadline_at` (`TIMESTAMP`, nullable): Timestamp for 3-day SLA countdown (`created_at + 72 hours`).
  - `rejection_reason` (`VARCHAR(255)`, nullable): Reason selected by seller if order is rejected.
  - `cancelled_at` (`TIMESTAMP`, nullable): Date/time when order was cancelled.
  - `cancelled_by` (`VARCHAR(50)`, nullable): Actor who cancelled (`seller`, `buyer`, `system`, `admin`).
  - `buyer_notified_at` (`TIMESTAMP`, nullable): Timestamp of buyer notification.

---

#### 3. `companies` Table
- **Columns Added**:
  - `minordervalue` (`DECIMAL(10,2)`, default `0`): Minimum Order Value threshold required to checkout.
  - `total_orders_received` (`INT`, default `0`): Running total count of orders received.
  - `total_orders_rejected` (`INT`, default `0`): Running total count of rejected orders.
  - `rejection_rate` (`DECIMAL(5,2)`, default `0.00`): Percentage metric of rejected orders for seller vendor scoring.

---

### C. Database Seeders Created

#### `DemoMultiCategoryProductSeeder.php`
- **File**: [`database/seeders/DemoMultiCategoryProductSeeder.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/database/seeders/DemoMultiCategoryProductSeeder.php)
- **Purpose**: Automatically seeds 12 rich multi-category products with Two-Price SKU matrices across:
  - **Paints & Emulsions**: Apcolite Gloss Enamel, Royale Luxury Emulsion, Tractor Economy Paint.
  - **Fabrics & Textiles**: Chanderi Pure Cotton, Kanchipuram Silk, Organic Khadi Linen.
  - **Garments & Apparel**: Oxford Shirts, Embroidered Anarkali Kurtis, Tailored Chinos.
  - **Tools & Accessories**: Paint Roller Kits, Angled Bristle Brushes, Painter Masking Tape.

---

## 2. Eloquent Models & Relationships Added

| Model | File | Key Additions / Relationships |
| :--- | :--- | :--- |
| **`CompanyAuditLog`** | [`app/Models/CompanyAuditLog.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Models/CompanyAuditLog.php) | `$fillable`, `$casts = ['old_values'=>'array', 'new_values'=>'array']`, helper method `logChange()`, `belongsTo(Company::class)`, `belongsTo(User::class)`. |
| **`PaintPriceAdjustment`**| [`app/Models/PaintPriceAdjustment.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Models/PaintPriceAdjustment.php) | `$fillable`, `$casts = ['scope_json'=>'array', 'preview_data'=>'array']`, `belongsTo(Product::class)`, `belongsTo(User::class)`. |
| **`Company`** | [`app/Models/Company.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Models/Company.php) | Added `auditLogs()` and `priceAdjustments()` `hasMany` relationships. |
| **`Product`** | [`app/Models/Product.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Models/Product.php) | Added `attributes()` alias method pointing to `ProductAttributes::class`. |
| **`Order`** | [`app/Models/Order.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Models/Order.php) | Added `protected $guarded = [];` to allow safe mass assignment on status updates. |
| **`ProductAttributes`** | [`app/Models/ProductAttributes.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Models/ProductAttributes.php) | Added `seller_price`, `commission_rate`, `pack_litres` to fillable attributes. |

---

## 3. Full Codebase Changes & Systems Implemented

### 1. Universal Smart Pricing & Two-Price Matrix Engine
- **Service**: [`app/Services/PaintPricingService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/PaintPricingService.php)
- **Controller**: [`app/Http/Controllers/PaintPricingController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/PaintPricingController.php)
- **View**: [`resources/views/product/paint_pricing.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/product/paint_pricing.blade.php)
- **Key Capabilities**:
  - Multi-tier pricing calculation: `Customer Price = Factory Price * (1 + Commission / 100)`.
  - Bulk adjustment math for **₹/Litre**, **Percentage (%)**, and **Fixed Rupee (₹)**.
  - Category-wide preview and atomic apply endpoints (`/seller/paint-pricing/category/preview` and `/apply`).
  - Interactive single-SKU price override calculator with instant preview.

---

### 2. 3-Day Seller Response Time SLA & Auto-Cancellation Cron
- **Artisan Command**: [`app/Console/Commands/AutoExpirePendingOrders.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Console/Commands/AutoExpirePendingOrders.php)
- **Kernel Scheduler**: [`app/Console/Kernel.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Console/Kernel.php) (Registered hourly schedule)
- **Seller Order UI**: [`resources/views/order/sellerorderlist.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/order/sellerorderlist.blade.php)
- **Key Capabilities**:
  - Live color-coded countdown timer in seller dashboard (`🟢 Safe`, `🟡 Action Required`, `🔴 Expired`).
  - Auto-cancels orders exceeding 72 hours without seller response.
  - Triggers automated refund, penalizes seller rejection score, and sends alternative sellers to buyer.

---

### 3. Alternative Sellers Recommendation System
- **Service**: [`app/Services/AlternativeSellerService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/AlternativeSellerService.php)
- **REST Endpoint**: `GET /api/customer/orders/{id}/alternative-sellers`
- **Key Capabilities**:
  - Finds top 3 verified replacement manufacturers in the same category.
  - Ranks alternative sellers using a composite score based on low rejection rates, minimum order value, and competitive factory pricing.

---

### 4. Multi-Channel Notification Router
- **Service**: [`app/Services/NotificationService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/NotificationService.php)
- **Controller**: [`app/Http/Controllers/NotificationController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/NotificationController.php)
- **Email Templates**:
  - [`resources/views/emails/order_new_seller.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/emails/order_new_seller.blade.php)
  - [`resources/views/emails/order_cancelled_buyer.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/emails/order_cancelled_buyer.blade.php)
- **Key Capabilities**:
  - Real-time in-app notification center via AJAX polling (15s interval).
  - Firebase Cloud Messaging (FCM Mobile Push) dispatcher with local mock logging.
  - WhatsApp Cloud / Wati message formatter with `+91` normalization.
  - Unread orders login popup modal on seller login.

---

### 5. Automated Refund & Webhook Manager
- **Service**: [`app/Services/PaymentRefundService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/PaymentRefundService.php)
- **Webhook Endpoint**: `POST /api/payment/webhook`
- **Key Capabilities**:
  - Executes atomic refunds in database transactions.
  - Updates `order_status` and `order_tracks` to `'Cancelled'`.
  - Credits buyer in-app `wallet` for immediate reordering.
  - Integrates with Razorpay/Cashfree gateway refund APIs.

---

### 6. Filament Admin Control Suite with Modals
- **Resource**: [`app/Filament/Resources/CompanyResource.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/CompanyResource.php)
- **RelationManagers**:
  1. [`PriceAdjustmentsRelationManager.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/CompanyResource/RelationManagers/PriceAdjustmentsRelationManager.php) with **SKU Breakdown Modal** ([`price-adjustment-modal.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/filament/components/price-adjustment-modal.blade.php)).
  2. [`AuditLogsRelationManager.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/CompanyResource/RelationManagers/AuditLogsRelationManager.php) with **JSON Diff Payload Modal** ([`audit-log-modal.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/filament/components/audit-log-modal.blade.php)).
- **Order Resource**: [`app/Filament/Resources/OrderResource.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/OrderResource.php) with full 7-color status spectrum badges.

---

### 7. Customer Mobile Experience Simulator
- **Controller**: [`app/Http/Controllers/AppPreviewController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/AppPreviewController.php)
- **View**: [`resources/views/customer/app_preview.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/customer/app_preview.blade.php)
- **Route**: `GET /customer/app-preview` ([http://127.0.0.1:8000/customer/app-preview](http://127.0.0.1:8000/customer/app-preview))
- **Key Capabilities**: Interactive in-browser phone simulator demonstrating catalog discovery, two-price calculation, rejected order alternative sellers with 1-click reorder, and live notification feeds.

---

## 4. Bug Fixes & Security Hardening Log

| Bug / Vulnerability | File | Resolution |
| :--- | :--- | :--- |
| **Plaintext Customer Passwords** | [`CustomerController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CustomerController.php) | Converted raw string comparisons to Bcrypt `Hash::check()` with automatic hashing on register/login. |
| **Customer Registration Crash** | [`CustomerController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CustomerController.php) | Removed non-existent column insertions (`lastname`, `profilephoto`). |
| **OTP Null Pointer Crash** | [`CustomerController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CustomerController.php) | Added safe null checks and early returns. |
| **SQL `ONLY_FULL_GROUP_BY` Strict Mode** | [`BrandController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/BrandController.php) | Added `adminresponse` to `groupBy()` for strict SQL compatibility. |
| **Company MOV Blank Screen** | [`CompanyController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CompanyController.php) | Added explicit return redirect with success flash message. |
| **Filament Table Action Modal Trigger** | [`PriceAdjustmentsRelationManager.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/CompanyResource/RelationManagers/PriceAdjustmentsRelationManager.php) | Bound `Forms\Components\View::make(...)` and dynamic `$getRecord()` resolution. |
| **404 Broken Images in Sidebar** | [`sidebar.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/sidebar.blade.php) | Replaced hardcoded broken avatar paths with dynamic Support & Help Tickets dropdown with inline SVG icons. |
| **Order Tax Splitting** | [`OrderController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/OrderController.php) | Added buyer vs seller state check to split GST into Intra-state (CGST+SGST) vs Inter-state (IGST). |

---

## 5. Summary of Modified & Created Files

### Newly Created Files (14 Files):
1. `database/migrations/2026_01_02_000001_add_seller_pricing_to_product_attributes_table.php`
2. `database/migrations/2026_01_02_000002_create_paint_price_adjustments_table.php`
3. `database/migrations/2026_08_24_101313_create_company_audit_logs_table.php`
4. `database/seeders/DemoMultiCategoryProductSeeder.php`
5. `app/Models/CompanyAuditLog.php`
6. `app/Models/PaintPriceAdjustment.php`
7. `app/Services/PaintPricingService.php`
8. `app/Services/AlternativeSellerService.php`
9. `app/Services/NotificationService.php`
10. `app/Services/PaymentRefundService.php`
11. `app/Http/Controllers/AppPreviewController.php`
12. `resources/views/customer/app_preview.blade.php`
13. `resources/views/emails/order_new_seller.blade.php`
14. `resources/views/emails/order_cancelled_buyer.blade.php`

### Existing Files Enhanced / Modified (12 Files):
1. `app/Filament/Resources/CompanyResource.php`
2. `app/Filament/Resources/CompanyResource/RelationManagers/PriceAdjustmentsRelationManager.php`
3. `app/Filament/Resources/CompanyResource/RelationManagers/AuditLogsRelationManager.php`
4. `resources/views/filament/components/price-adjustment-modal.blade.php`
5. `resources/views/filament/components/audit-log-modal.blade.php`
6. `app/Http/Controllers/PaintPricingController.php`
7. `resources/views/product/paint_pricing.blade.php`
8. `app/Http/Controllers/CompanyController.php`
9. `app/Http/Controllers/CustomerController.php`
10. `app/Console/Commands/AutoExpirePendingOrders.php`
11. `routes/web.php`
12. `routes/api.php`
