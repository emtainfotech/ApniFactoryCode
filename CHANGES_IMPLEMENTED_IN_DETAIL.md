# 🛠️ ApniFactory: Detailed Record of Changes Implemented

This document provides a comprehensive, file-by-file record of all features, architectures, bug fixes, database migrations, and enhancements implemented across the ApniFactory backend codebase.

---

## 📑 Summary of Major Systems Implemented

1. **7 Critical Pre-Launch Business Requirements**:
   - Seller Real-Time Multi-Channel Notifications (Push, Email, Database Badges, Login Unread Orders Modal).
   - 3-Day Seller Response Time SLA (Live Countdown UI + Automated Auto-Cancel Cron Engine).
   - Buyer Notifications & Alternative Sellers Finder (Top 3 replacement sellers with ratings & pricing).
   - Filament Admin Full Color-Coded Order Spectrum Badges.
   - Rejection Reason & Seller Performance Audit Tracking with Rejection Rate Calculations.
   - Seller Minimum Order Value (MOV) Enforcement with Cart Blockers & Visual Progress Bars.
   - Full Color Range & Pack Sizes with Two-Price System (Factory Base Price vs Marketplace Customer Price).
2. **Universal Smart Pricing Engine (Product-Wise & Category-Wise)**:
   - Bulk Price Adjustments (Per Litre, Percentage %, Fixed Rupee ₹).
   - Scope Control (Entire Category, Entire Product Family, Specific Shades, Specific Pack Sizes).
   - Real-Time Live Preview Calculation Engine.
   - Atomic DB Transaction Price Application.
3. **Company Change Logs & Audit History in Filament Admin**:
   - Complete change tracking for Minimum Order Value, Passwords, Store Banners, Logos, and Price Adjustments.
   - RelationManagers for **Pricing History & Adjustments** and **Company Activity Logs**.
   - Interactive Modal views with before/after SKU breakdowns and JSON diff payloads.
4. **Codebase Bug Fixes & Security Hardening**:
   - Customer Auth password migration to Bcrypt `Hash::make()` with legacy auto-upgrade.
   - Safe OTP null handling preventing uncaught null pointer exceptions.
   - SQL strict mode resolution for MySQL/MariaDB `ONLY_FULL_GROUP_BY`.
   - Dynamic Support & Help Tickets dropdown replacing 404 broken image links.
   - Multi-category demo product seeding across Paints, Fabrics, Garments, and Accessories.

---

## 🗄️ Database Migrations & Schema Changes

### 1. [`2026_08_24_084500_add_pre_launch_critical_columns.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/database/migrations/2026_08_24_084500_add_pre_launch_critical_columns.php)
- **`orders` table**:
  - `seller_deadline_at`: Timestamp for 3-day SLA countdown.
  - `rejection_reason`: Reason selected by seller upon order rejection.
  - `cancelled_at`: Timestamp when order was cancelled.
  - `cancelled_by`: Actor who cancelled (`seller`, `buyer`, `system`, `admin`).
  - `buyer_notified_at`: Timestamp of buyer rejection notification.
- **`companies` table**:
  - `minordervalue`: Seller's minimum order threshold.
  - `total_orders_received`: Running count of received orders.
  - `total_orders_rejected`: Running count of rejected orders.
  - `rejection_rate`: Calculated percentage of rejected orders.

### 2. [`2026_01_02_000002_create_paint_price_adjustments_table.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/database/migrations/2026_01_02_000002_create_paint_price_adjustments_table.php)
- **`paint_price_adjustments` table**:
  - `id`, `user_id`, `product_id`, `adjustment_type`, `adjustment_value`, `scope_type`, `scope_json`, `affected_count`, `preview_data`, `created_by`, `timestamps`.

### 3. [`2026_08_24_101313_create_company_audit_logs_table.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/database/migrations/2026_08_24_101313_create_company_audit_logs_table.php)
- **`company_audit_logs` table**:
  - `id`, `company_id`, `user_id`, `actor_name`, `actor_role`, `action_type`, `title`, `description`, `old_values`, `new_values`, `ip_address`, `timestamps`.

---

## 💻 Backend Services & Controllers

### 1. [`App\Services\AlternativeSellerService`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/AlternativeSellerService.php)
- **Purpose**: Finds the top 3 alternative sellers when an order is rejected.
- **Algorithm**:
  - Queries active sellers offering the same product or main category.
  - Excludes the rejecting seller.
  - Prioritizes sellers by minimum order value, proximity, and low rejection rates.
  - Formats payload with seller company name, product title, factory price, pack sizes, and rating.

### 2. [`App\Services\PaintPricingService`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/PaintPricingService.php)
- **Purpose**: Core engine for multi-tier pricing (Factory Price vs Customer Price) and bulk adjustments.
- **Key Methods**:
  - `parsePackLitres($boxPacking)`: Converts strings like `'1 L'`, `'4 Ltr'`, `'500 ML'`, `'20 Litres'` into precise float litrage.
  - `getEffectiveCommissionRate($sellerId, $productId)`: Dynamic commission rate retrieval (defaults to 25%).
  - `calculatePreview($productId, $type, $value, $scope)`: Calculates non-persisted SKU pricing preview.
  - `applyAdjustment($productId, $type, $value, $scope, $userId)`: Applies price adjustments atomically in a DB transaction and logs to `paint_price_adjustments` and `company_audit_logs`.
  - `calculateCategoryPreview($categoryId, $type, $value, $userId)`: Category-wide preview across all products in a category.
  - `applyCategoryAdjustment($categoryId, $type, $value, $userId)`: Atomic category-wide price update across all products and SKUs in a category.

### 3. [`App\Services\NotificationService`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/NotificationService.php)
- **Purpose**: Multi-channel notification router for Admin, Seller, and Buyer.
- **Channels Supported**: In-App Database Notifications, FCM Mobile Push, WhatsApp Cloud API, and Transactional Email.

### 4. [`App\Http\Controllers\PaintPricingController`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/PaintPricingController.php)
- **Endpoints**:
  - `GET /seller/paint-pricing`: Seller web interface with Category filter and Product family selector.
  - `GET /seller/paint-pricing/data/{id}`: AJAX/API SKU pricing matrix.
  - `POST /seller/paint-pricing/preview`: Preview calculation endpoint.
  - `POST /seller/paint-pricing/apply`: Atomic bulk price update.
  - `POST /seller/paint-pricing/category/preview`: Category-wise bulk pricing preview.
  - `POST /seller/paint-pricing/category/apply`: Category-wise bulk price application.
  - `POST /seller/paint-pricing/sku-override`: Individual SKU price override.

### 5. [`App\Http\Controllers\CompanyController`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CompanyController.php)
- Added change audit logging (`CompanyAuditLog::logChange`) for:
  - Minimum Order Value changes.
  - Password updates.
  - Store banner uploads.
  - Company logo updates.

### 6. [`App\Console\Commands\CancelExpiredOrders`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Console/Commands/CancelExpiredOrders.php)
- **Artisan Command**: `php artisan app:cancel-expired-orders`
- **Execution**: Scans for orders with `seller_deadline_at < now()` and `status = 'Pending'`. Cancels expired orders, marks payment as `'Refunded'`, updates seller rejection metrics, and triggers alternative seller recommendations for buyers.

---

## 🎨 Frontend & UI Enhancements

### 1. Smart Pricing View ([`resources/views/product/paint_pricing.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/product/paint_pricing.blade.php))
- Category Filter dropdown to filter products or switch into Category Mode.
- Scope Selector with options for:
  - `📂 Entire Category (All Products in Category)`
  - `🎨 Entire Paint Family (All Shades & Sizes)`
  - `🎨 Specific Shades Only`
  - `📦 Specific Pack Sizes Only`
- Live interactive preview table with color swatches, litrage indicators, old factory/customer prices, delta badges, and new prices.
- Live single SKU price edit calculator.

### 2. Seller Layout & Navbar ([`resources/views/sidebar.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/sidebar.blade.php))
- Real-time notification polling (15s interval) in the top navigation bar with unread count badges.
- Dedicated Support & Help Tickets dropdown querying active support tickets and rendering SVG badges (eliminating 404 avatar errors).

### 3. Seller Order Management ([`resources/views/order/sellerorderlist.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/order/sellerorderlist.blade.php))
- Real-time 3-Day SLA countdown timer with color-coded badges (`🟢 Safe`, `🟡 Action Required`, `🔴 Expired`).
- Rejection reason selector modal with mandatory reason selection.
- Login modal popup for sellers highlighting pending unread orders.

### 4. Filament Admin Company Page ([`app/Filament/Resources/CompanyResource.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/CompanyResource.php))
- Full Order status color-coded badges.
- Registered **`PriceAdjustmentsRelationManager`** for viewing price adjustments history with SKU Breakdown modal.
- Registered **`AuditLogsRelationManager`** for viewing all company profile and settings activity logs with payload comparison modal.

---

## 🛡️ Bug Fixes & Code Quality Hardening

| Issue | File | Resolution |
| :--- | :--- | :--- |
| Customer Password Insecurity | [`CustomerController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CustomerController.php) | Replaced raw string comparison with `Hash::check()` and Bcrypt auto-upgrade. |
| Customer Register Missing Columns | [`CustomerController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CustomerController.php) | Fixed non-existent database column insertions (`lastname`, `profilephoto`). |
| Safe OTP Handling | [`CustomerController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CustomerController.php) | Added safe null checks preventing fatal crashes on unverified users. |
| Coupon Controller Signature | [`CouponController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CouponController.php) | Added `Request $request` to `show(Request $request, $id = null)` avoiding missing argument errors. |
| Company MOV Update Missing Return | [`CompanyController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/CompanyController.php) | Added explicit return redirect preventing blank page responses. |
| SQL Strict Mode in Brands List | [`BrandController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/BrandController.php) | Included `adminresponse` in `groupBy()` to comply with MySQL/MariaDB strict mode. |
| Empty Cart Validation | [`OrderController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/OrderController.php) | Replaced empty string check with `$cartdata->isEmpty()` to prevent invalid order creation. |
| State GST Tax Calculation | [`OrderController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/OrderController.php) | Added state check to split tax into CGST + SGST (Intra-state) vs IGST (Inter-state). |
| Product Status Query Compatibility | [`PaintPricingController.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/PaintPricingController.php) | Broadened status queries to accept `'Active'`, `'1'`, and `1` across all product queries. |
