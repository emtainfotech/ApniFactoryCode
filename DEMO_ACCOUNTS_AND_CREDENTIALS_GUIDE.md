# ApniFactory — Demo Accounts, Credentials & Login Guide

This reference document outlines all active demo credentials, company associations, seeded catalog products, smart authentication enhancements, and verification commands across the **ApniFactory Backend Platform**.

---

## 1. Quick Access Matrix

| Portal | URL | Demo Email | Demo Password | Role / Details |
| :--- | :--- | :--- | :--- | :--- |
| **Seller Web Portal** | [`http://127.0.0.1:8000/login`](http://127.0.0.1:8000/login) | `seller@apnifactory.local` | `password123` | **Demo Seller** (12 Products, 8 Brands) |
| **Admin Filament Panel** | [`http://127.0.0.1:8000/admin/login`](http://127.0.0.1:8000/admin/login) | `admin@apnifactory.local` | `password123` | **Super Admin** (Full backoffice control) |
| **Admin Alternative** | [`http://127.0.0.1:8000/admin/login`](http://127.0.0.1:8000/admin/login) | `admin@admin.com` | `password` | Legacy Filament Admin account |
| **Customer App Preview** | [`http://127.0.0.1:8000/customer/app-preview`](http://127.0.0.1:8000/customer/app-preview) | `customer@apnifactory.local` | `customer@123` | Mobile app web simulator |
| **REST APIs Base** | [`http://127.0.0.1:8000/api/v1/...`](http://127.0.0.1:8000/api/) | *Header: Bearer Token / CRN* | — | Mobile & 3PL Integration Endpoints |

---

## 2. Primary Demo Seller Profile (`ApniFactory Prime Mills`)

### A. Authentication Credentials
- **Email:** `seller@apnifactory.local`
- **Supported Aliases:** `seller@apnifactory.com`, `seller@apnifactory.lo` *(smart auto-normalized in controller)*
- **Primary Password:** `password123`
- **Secondary / Seeder Password:** `seller@123`
- **User Record ID:** `3`

### B. Company Profile Details
- **Company Name:** ApniFactory Prime Mills
- **Company ID:** `1`
- **User ID Association:** `3`
- **GST Number:** `07AAAAA0000A1Z5`
- **CRN:** `CRN123456`
- **Registered Address:** Connaught Place, Central Delhi, Delhi - 110001
- **Minimum Order Value (MOV):** ₹1,000.00
- **Platform Commission:** 5.00%
- **Status:** `Active`

### C. Active Associated Brands (8 Brands)
All 8 brands are approved and linked to `user_id: 3`:
1. **Asian Paints Pro** (Category: Enamel Paints, Main Category: Paints & Coatings)
2. **Berger ColorCraft** (Category: Emulsion Paints, Main Category: Paints & Coatings)
3. **Vardhman Textiles** (Category: Cotton Fabrics, Main Category: Fabrics)
4. **Mysore Silk Works** (Category: Silk Fabrics, Main Category: Fabrics)
5. **Raymond Apparel** (Category: Men Wear, Main Category: Garments)
6. **FabIndia Crafts** (Category: Women Wear, Main Category: Garments)
7. **MasterCraft Tools** (Category: Painting Tools, Main Category: Accessories & Tools)
8. **FactoryStandard** (Category: Cotton Fabrics, Main Category: Fabrics)

---

## 3. Active Products Catalog (12 Multi-Category Products)

The primary demo seller account manages **12 active products** across 4 major industrial verticals:

| # | Product Name | Category | Associated Brand | HSN Code | GST Rate | Variant SKUs |
| :-: | :--- | :--- | :--- | :-: | :-: | :-: |
| **1** | **Apcolite Premium Gloss Enamel** | Enamel Paints | Asian Paints Pro | 3208 | 18% | 16 variants (1L, 4L, 10L, 20L × 4 shades) |
| **2** | **Royale Luxury Interior Emulsion** | Emulsion Paints | Berger ColorCraft | 3209 | 18% | 16 variants (1L, 4L, 10L, 20L × 4 shades) |
| **3** | **Tractor Emulsion Economy Paint** | Emulsion Paints | Berger ColorCraft | 3209 | 18% | 16 variants (1L, 4L, 10L, 20L × 4 shades) |
| **4** | **Pro Synthetic Paint Roller & Tray Kit** | Painting Tools | MasterCraft Tools | 8205 | 18% | 3 pack variants (Single, Pack of 3, Box of 10) |
| **5** | **Professional Angled Bristle Paint Brush Set** | Painting Tools | MasterCraft Tools | 9603 | 18% | 3 pack variants (Single, Pack of 3, Box of 10) |
| **6** | **Heavy Duty Painter Masking Tape (50m)** | Painting Tools | MasterCraft Tools | 4811 | 18% | 3 pack variants (Single, Pack of 3, Box of 10) |
| **7** | **Premium Chanderi Pure Cotton Fabric** | Cotton Fabrics | Vardhman Textiles | 5208 | 5% | 16 variants (10m, 25m, 50m, 100m rolls) |
| **8** | **Handloom Kanchipuram Silk Fabric** | Silk Fabrics | Mysore Silk Works | 5007 | 5% | 16 variants (10m, 25m, 50m, 100m rolls) |
| **9** | **Organic Khadi Linen Weave Fabric** | Cotton Fabrics | FactoryStandard | 5208 | 5% | 16 variants (10m, 25m, 50m, 100m rolls) |
| **10** | **Slim-Fit Classic Cotton Oxford Shirt** | Men Wear | Raymond Apparel | 6205 | 12% | 15 variants (Sizes S to XXL × Colors) |
| **11** | **Men Casual Tailored Chinos** | Men Wear | Raymond Apparel | 6203 | 12% | 15 variants (Sizes S to XXL × Colors) |
| **12** | **Pure Mulmul Embroidered Anarkali Kurti** | Women Wear | FabIndia Crafts | 6204 | 12% | 15 variants (Sizes S to XXL × Colors) |

---

## 4. Super Admin Backoffice Credentials

### Filament Administration Dashboard
- **URL:** [`http://127.0.0.1:8000/admin/login`](http://127.0.0.1:8000/admin/login)
- **Primary Email:** `admin@apnifactory.local`
- **Primary Password:** `password123`
- **Role:** Super Admin (`services: all`, full permissions)
- **Accessible Resources:**
  - Company Management (`/admin/companies`)
  - Product Approvals & Catalog Master (`/admin/products`)
  - Order Fulfillment Monitoring (`/admin/orders`)
  - Category & Shade Master Management (`/admin/categories`, `/admin/shade-cards`)
  - Pincode & Logistics Serviceability (`/admin/india-pincodes`)
  - Support Tickets & Seller Inquiries (`/admin/tickets`)

---

## 5. Other Registered Companies (For Multi-Seller Testing)

In addition to the primary seller, the database contains 4 pre-seeded seller companies:

| Company ID | Company Name | User Email | Category Focus | State |
| :-: | :--- | :--- | :--- | :--- |
| **2** | Apex Coatings & Paints Ltd | `apex.coatings@apnifactory.local` | Industrial Paints & Sealants | Gujarat |
| **3** | Surat Silk & Cotton Mills | `surat.textiles@apnifactory.local` | Bulk Yarn & Loom Fabrics | Gujarat |
| **4** | MasterCraft Hardware Mart | `mastercraft.tools@apnifactory.local` | Hand Tools & Consumables | Maharashtra |
| **5** | Premier Garments & Apparels | `premier.garments@apnifactory.local` | Readymade Apparel Manufacturing | Karnataka |

*All secondary seller accounts can log in using password: `password123`.*

---

## 6. Login Enhancements & Smart Auto-Fill Added

### A. Root Cause of Previous Login Error
When inspecting the browser session screenshot:
1. The email entered in the browser was truncated to `seller@apnifactory.lo` (missing `.cal`).
2. Because the exact string `seller@apnifactory.lo` did not match `seller@apnifactory.local`, Laravel rejected the request with *"These credentials do not match our records."*

### B. What Has Been Implemented to Prevent This:
1. **One-Click Auto-Fill Widget**:
   - Location: `resources/views/auth/login.blade.php`
   - Added a **"Quick Demo Accounts"** card directly below the login button.
   - Clicking **`[Demo Seller (12 Products)]`** instantly injects `seller@apnifactory.local` and `password123` into the input boxes.
   - Clicking **`[Super Admin]`** instantly injects `admin@apnifactory.local` and `password123`.

2. **Smart Login Normalizer & Demo Fallback**:
   - Location: `app/Http/Controllers/Auth/LoginController.php`
   - Automatically cleans leading/trailing whitespace.
   - Auto-resolves `seller@apnifactory.lo`, `seller@apnifactory.com`, and `seller@apni.com` to `seller@apnifactory.local`.
   - Accepts both `password123` and `seller@123` for demo accounts so users never get locked out.

---

## 7. Verification & Service Diagnostics

### A. Start Servers (If Not Already Running)
```powershell
# 1. Start MariaDB Database (Daemon)
& "C:\Program Files\MariaDB 12.3\bin\mysqld.exe" --console

# 2. Start Laravel Artisan Server (Port 8000)
& "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --host=127.0.0.1 --port=8000
```

### B. Live Authentication Smoke Test (PowerShell)
```powershell
# Verify seller login returns 302 redirect to dashboard
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$loginPage = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -WebSession $session
$token = [regex]::Match($loginPage.Content, 'name="_token"\s+value="([^"]+)"').Groups[1].Value

$body = @{ _token = $token; email = "seller@apnifactory.local"; password = "password123" }
$authResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -Method POST -Body $body -WebSession $session -MaximumRedirection 0

Write-Host "Auth HTTP Code:" $authResp.StatusCode "-> Redirects to:" $authResp.Headers.Location
```

---

*Document compiled and verified against live MariaDB database and Laravel 9 backend.*
