# 📋 ApniFactory: Requirements Fulfillment & Deployment Status

This document tracks the status of all business requirements, backend services, external integrations, and mobile frontend connectivity for the ApniFactory platform.

---

## 📌 Executive Status Table

| Category | Requirement / Scope | Status | Implementation Details |
| :--- | :--- | :--- | :--- |
| 🔔 **Multi-Channel Notifications** | Push (FCM), WhatsApp, Email, In-App DB | 🟢 **100% Implemented** | [`NotificationService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/NotificationService.php) with multi-channel router, template rendering, and safe local fallback. |
| 🔄 **Alternative Sellers Finder** | Top 3 Replacement Sellers on Rejection | 🟢 **100% Implemented** | [`AlternativeSellerService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/AlternativeSellerService.php) scoring sellers by MOV, proximity, and low rejection rates. |
| 💳 **Automated Refund Engine** | Instant Buyer Refund on Cancel/Reject | 🟢 **100% Implemented** | [`PaymentRefundService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/PaymentRefundService.php) handling gateway refunds, wallet credits, and webhooks. |
| ⏱️ **3-Day SLA & Auto-Cancellation** | Countdown UI + Background Cron Engine | 🟢 **100% Implemented** | [`AutoExpirePendingOrders.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Console/Commands/AutoExpirePendingOrders.php) registered in Console Kernel to auto-cancel and refund. |
| 🎨 **Smart Pricing Matrix Manager** | Factory Price vs Customer Markup | 🟢 **100% Implemented** | [`PaintPricingService.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/PaintPricingService.php) with Category-wise & Family-wise bulk adjustments (₹/L, %, Fixed ₹). |
| 📊 **Admin Audit & Change Logs** | Company MOV, Password, Banner & Pricing | 🟢 **100% Implemented** | [`PriceAdjustmentsRelationManager`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/CompanyResource/RelationManagers/PriceAdjustmentsRelationManager.php) & [`AuditLogsRelationManager`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Filament/Resources/CompanyResource/RelationManagers/AuditLogsRelationManager.php) with interactive modals. |
| 📱 **Mobile App Experience Simulator** | In-Browser Mobile Experience & APIs | 🟢 **100% Implemented** | [`/customer/app-preview`](http://127.0.0.1:8000/customer/app-preview) simulating products, rejected order alternatives, and in-app alerts. |
| 🔑 **Production Third-Party Keys** | Live FCM, WhatsApp, SMTP, Gateway Keys | 🟡 **Ready for Config** | Supported via `.env` variables (`FCM_SERVER_KEY`, `WHATSAPP_API_TOKEN`, `MAIL_HOST`, `RAZORPAY_KEY`). |

---

## 🛠️ Details of Completed & Built-In Services

### 1. 🔔 Multi-Channel Notification Router (`NotificationService`)
- **Class**: [`App\Services\NotificationService`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/NotificationService.php)
- **Features**:
  - Automatically inserts unread notifications into the `notifications` table for real-time web & mobile notification centers.
  - Sends high-priority push payloads to Firebase Cloud Messaging (FCM) using device tokens.
  - Formats and normalizes phone numbers (`+91` prefixing) to dispatch WhatsApp Business Cloud / Wati alerts.
  - Renders responsive HTML emails via Blade templates (`emails.order_new_seller`, `emails.order_cancelled_buyer`).
  - Includes safe local development mock dispatch with detailed logs.

### 2. 🔄 Alternative Sellers Recommendation Engine (`AlternativeSellerService`)
- **Class**: [`App\Services\AlternativeSellerService`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/AlternativeSellerService.php)
- **API Endpoint**: `GET /api/customer/orders/{id}/alternative-sellers`
- **Features**:
  - Automatically activates when an order is rejected or cancelled due to 72-hour SLA expiration.
  - Excludes the rejecting seller and searches for verified active manufacturers with matching product categories.
  - Ranks alternatives using a composite score based on low rejection rates, minimum order value thresholds, and competitive factory pricing.

### 3. 💳 Automated Refund & Webhook Manager (`PaymentRefundService`)
- **Class**: [`App\Services\PaymentRefundService`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Services/PaymentRefundService.php)
- **Webhook Endpoint**: `POST /api/payment/webhook`
- **Features**:
  - Executes atomic database transactions to record refund records in `transections` and credits in-app buyer `wallet`.
  - Integrates with Razorpay / Cashfree refund APIs when live credentials are provided.
  - Automatically updates `order_status` and `order_tracks` records to `'Cancelled'`.
  - Automatically dispatches buyer refund notifications across push, email, and in-app notifications.

### 4. ⏱️ 3-Day SLA Expiration Cron Engine
- **Command**: `php artisan orders:auto-expire-pending`
- **Schedule**: Hourly execution in [`app/Console/Kernel.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Console/Kernel.php)
- **Features**:
  - Scans for orders exceeding 72 hours without seller acceptance.
  - Automatically cancels expired orders, triggers `PaymentRefundService`, notifies buyer with top 3 alternative sellers, and penalizes the seller's rejection metric.

### 5. 📱 Interactive Mobile App Experience Simulator
- **Route**: `GET /customer/app-preview`
- **Controller**: [`App\Http\Controllers\AppPreviewController`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Http/Controllers/AppPreviewController.php)
- **View**: [`resources/views/customer/app_preview.blade.php`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/resources/views/customer/app_preview.blade.php)
- **Features**:
  - Provides a realistic, interactive in-browser mobile phone simulation.
  - Demonstrates customer product browsing, two-price calculation, real-time rejected order alternatives with 1-click reorder, and live notification feeds.

---

## 🔑 Production Environment Configuration (When Going Live)

All backend logic is fully written and tested. When deploying to production servers, simply configure your live credentials in the server `.env`:

```env
# 1. Firebase Cloud Messaging (For Mobile Push)
FCM_SERVER_KEY="your_production_fcm_server_key"
FCM_SENDER_ID="your_fcm_sender_id"

# 2. WhatsApp Business API / WATI
WHATSAPP_API_URL="https://api.wati.io/api/v1/sendTemplateMessage"
WHATSAPP_API_TOKEN="your_production_wati_token"

# 3. Transactional Mail Server
MAIL_MAILER=smtp
MAIL_HOST="smtp.sendgrid.net"
MAIL_PORT=587
MAIL_USERNAME="apikey"
MAIL_PASSWORD="your_smtp_api_key"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="support@apnifactory.com"
MAIL_FROM_NAME="ApniFactory"

# 4. Payment Gateway (Razorpay / Cashfree)
RAZORPAY_KEY="rzp_live_xxxxxxxx"
RAZORPAY_SECRET="xxxxxxxxxxxxxxxx"
```

---

## ⚙️ Production Crontab Setup

Add this line to your production Linux/Cloud server crontab (`crontab -e`):
```bash
* * * * * cd /path/to/ApniFactoryBackend && php artisan schedule:run >> /dev/null 2>&1
```
