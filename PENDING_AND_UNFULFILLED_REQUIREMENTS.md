# 📋 ApniFactory: Pending, External & Future Scope Requirements

This document outlines all requirements, external configurations, third-party credentials, and mobile application integrations that remain **pending**, **unfulfilled by external services**, or require **production deployment setup**.

---

## 📌 Executive Summary Table

| Category | Requirement / Scope | Status | Blocker / Dependency | Action Required |
| :--- | :--- | :--- | :--- | :--- |
| 📱 **Mobile Application** | Customer Mobile App Frontend UI/UX | 🔴 Separate Codebase | Mobile App Frontend Repo (Flutter / React Native) | Integrate mobile frontend with backend REST APIs (`/api/...`). |
| 🔔 **Push Notifications** | Production FCM / APNs Push Notification Delivery | 🟡 Config Pending | Google Firebase Server Key & FCM Service Account JSON | Place `firebase-credentials.json` or `.env` `FCM_SERVER_KEY` for live mobile device pushes. |
| 💬 **WhatsApp Alerts** | Live Production WhatsApp Cloud / Wati API | 🟡 Mock / Test Mode | Production WhatsApp Business API Token & Template Approval | Add production API token and approved message templates in `.env`. |
| 💳 **Payment Gateway** | Live UPI / Card / NetBanking Payment Webhooks | 🟡 Sandbox Mode | Production Razorpay / PhonePe Merchant ID & Secret Keys | Replace test gateway keys with production merchant credentials in `.env`. |
| ⏱️ **Cron Scheduling** | Background Automated Cancellation Cron Execution | 🟡 Server Crontab Setup | Linux / Windows Server Task Scheduler (`cron`) | Add `* * * * * php artisan schedule:run >> /dev/null 2>&1` to production server crontab. |
| 📧 **Transactional Email** | Live Production SMTP / SES / SendGrid Server | 🟡 Default / Local | Production SMTP Host, Port, and Authenticated User | Configure verified domain credentials in `.env` (`MAIL_MAILER`, `MAIL_HOST`). |

---

## 1. 📱 Mobile Application Frontend (Customer & Seller)

### Current Status:
- **Backend**: 100% of the backend REST APIs for authentication, products, multi-tier pricing, cart, order workflow, alternative sellers, notifications, and customer profiles are implemented and live.
- **Frontend**: The mobile application frontend codebase is maintained in a **separate project repository**.

### Remaining / Pending Steps:
1. **Connect Mobile Frontend to New Backend Endpoints**:
   - `GET /api/customer/orders/{id}/alternative-sellers`: Render alternative sellers recommendations on rejected order screens.
   - `GET /api/customer/notifications`: Render the in-app notification center.
   - `POST /api/customer/update-fcm-token`: Store device registration tokens on mobile login.
2. **Mobile Push Notification Receiver**:
   - Configure Firebase Cloud Messaging (FCM) client SDK inside the mobile app to receive background and foreground notification payloads.

---

## 2. 🔑 Third-Party Production Credentials & Environment Keys

The codebase has built-in drivers for WhatsApp, SMS, Email, and Push Notifications. To go live, the following environment variables must be populated in the production `.env`:

### A. Firebase Cloud Messaging (FCM)
```env
FCM_SERVER_KEY="your_production_fcm_server_key_here"
FCM_SENDER_ID="your_firebase_sender_id"
```
*Current state: Backend notification service fallbacks gracefully to database and in-app notifications if FCM keys are not configured.*

### B. WhatsApp Business API / WATI Gateway
```env
WHATSAPP_API_URL="https://api.wati.io/api/v1/sendTemplateMessage"
WHATSAPP_API_TOKEN="your_production_bearer_token"
```
*Current state: Helper functions are structured; requires live provider endpoint and approved WhatsApp message templates.*

### C. Transactional Email SMTP Server
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net (or mailgun / aws ses)
MAIL_PORT=587
MAIL_USERNAME="apikey"
MAIL_PASSWORD="your_production_api_key"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="notifications@apnifactory.com"
MAIL_FROM_NAME="ApniFactory"
```

---

## 3. ⚙️ Server-Level Crontab Setup

The backend Artisan command [`app:cancel-expired-orders`](file:///c:/Users/EPC001/Desktop/ApniFactoryBackend/app/Console/Commands/CancelExpiredOrders.php) has been created and registered in the Laravel Console Kernel to automatically cancel orders exceeding the 3-day seller response limit and trigger buyer refunds & alternative seller alerts.

### Required Server Action:
Add the standard Laravel scheduler entry to the production server crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 4. 🚀 Future Roadmap & Enhancements

1. **Automated Payment Gateway Refund Integration**:
   - Currently, order cancellation sets `payment_status = 'Refunded'` and logs the refund record. Integrating automated direct-to-source refunds via Razorpay/Cashfree Refund APIs can be enabled once production merchant accounts are connected.
2. **WebSocket / Pushpin Real-Time Broadcasting**:
   - Real-time seller and admin notification polling operates via low-latency AJAX (15s intervals) with local badge caching. For extreme high-concurrency scale, Pusher / Laravel Echo WebSockets can be connected.
