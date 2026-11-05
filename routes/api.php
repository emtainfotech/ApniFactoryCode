<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ProductReviewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\PaintPricingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

 Route::post('order/transection/testsuccess', [OrderController::class, "transectionfailed_app_testingconvertassuccess"]);
 Route::get('checkwhatsapp', [OrderController::class, "checkwhatsappmsg"]);
 Route::get('msg', [CustomerController::class, "sendnotification"]);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
////////login registration updateprofile
 Route::post('login', [CustomerController::class, "login"]);
 Route::post('register', [CustomerController::class, "register"]);
 Route::post('updateprofile/{id}', [CustomerController::class, "update"]);
 Route::post('changepassword/{id}', [CustomerController::class, "changepassword"]);
 Route::get('viewprofile/{id}', [CustomerController::class, "show"]);
 Route::post('userprofile', [CustomerController::class, "userprofile"]);

// Paint Family Smart Pricing APIs
Route::get('seller/paint-families/{id}/pricing', [PaintPricingController::class, 'getFamilyPricingData']);
Route::post('seller/paint-pricing/preview', [PaintPricingController::class, 'preview']);
Route::post('seller/paint-pricing/apply', [PaintPricingController::class, 'apply']);
Route::post('seller/paint-pricing/sku-override', [PaintPricingController::class, 'updateSingleSku']);
Route::get('seller/paint-pricing/audit/{id}', [PaintPricingController::class, 'auditHistory']);
 Route::post('verifyotp', [CustomerController::class, "verifyotp"]);
 Route::post('resetpassword', [CustomerController::class, "resetpassword"]);
 Route::get('statecitypincode', [AppController::class, "statecitypincode"]);
 /////homescreen
 Route::get('homescreen', [AppController::class, "homescreen"]);
 Route::get('randomproducts', [AppController::class, "randomproducts"]);
 Route::get('categoryscreen', [AppController::class, "secondscreen_category_company"]);
 Route::get('brandscreen',[AppController::class,"brandlist_bycompanyid"]);
 Route::get('companyscreen',[AppController::class,"companylist_bycategoryid"]);
 Route::get('productlist',[AppController::class,"productcategorylist_bybrandid"]);
 Route::get('productlistbycategory',[AppController::class,"productlistby_bybrandidandsid"]);
 
 Route::get('pages/{name?}', [PageController::class, "pagesforapp"]);
 
 Route::get('faq', [FaqController::class, "index"]);
 Route::get('feedback/{id}', [FeedbackController::class, "edit"]);
 Route::post('feedback', [FeedbackController::class, "store"]);
//  Route::get('coloursdata',[AppController::class,"colourdata"]);
//  Route::get('shardecard',[AppController::class,"shardecard"]);
//  Route::get('brandlistbyshadeid',[AppController::class,"brandaccordingsharcardid"]);
//  Route::get('productlistaccordingbrandandshade',[AppController::class,"productlistaccordingbrandandshade"]);
 ///////////cart
 Route::post('addtocart', [CartController::class, "addtocart"]);
 Route::post('increasecartqty', [CartController::class, "increasecartqty"]);
 Route::post('usercart', [CartController::class, "usercart"]);
 Route::post('deletecart', [CartController::class, "deletecart"]);
 Route::post('emptycart', [CartController::class, "emptycart"]);
 Route::post('deletecartattribute', [CartController::class, "deletecartattribute"]);
 Route::post('updatecartaddress', [CartController::class, "updatecartaddress"]);
 ///////////wishlist
 Route::post('addtowishlist', [CartController::class, "addtowishlist"]);
 Route::post('userwishlist', [CartController::class, "userwishlist"]);
 Route::post('deletewishlist', [CartController::class, "deletewishlist"]);
 
 Route::post('checkavailability', [CartController::class, "checkavailability"]);
 
 ///////////product
 Route::post('productdetail', [ProductController::class, "productdetail"]);
 Route::post('productattributeprice', [ProductController::class, "productattributeprice"]);
 Route::post('productreview', [ProductReviewsController::class, "store"]);
 
 ///////////customer address
 Route::post('address', [CustomerAddressController::class, "index"]);
 Route::post('addaddress', [CustomerAddressController::class, "store"]);
 Route::get('address/{id}', [CustomerAddressController::class, "show"]);
 Route::post('address/{id}', [CustomerAddressController::class, "update"]);
 Route::delete('address/{id}', [CustomerAddressController::class, "destroy"]);
 ///////////coupon
 Route::post('couponlist', [CouponController::class, "listforapp"]);
 Route::post('applycoupon', [CouponController::class, "applycoupon"]);
 Route::get('coupon', [CouponController::class, "index"]);
 Route::get('coupon/{id}', [CouponController::class, "show"]);
 ///////////////////order
 Route::post('order', [OrderController::class, "placeorder"]);
 Route::post('orderhistory', [OrderController::class, "orderhistory"]);
 Route::post('orderdetail', [OrderController::class, "orderdetailforapi"]);
 Route::post('orderstatus', [OrderController::class, "orderstatus_app"]);
 Route::post('order/alternatives', [OrderController::class, "getAlternativeSellersForOrder"]);
 Route::get('order/{orderno}/alternatives', [OrderController::class, "getAlternativeSellersForOrder"]);
 
 Route::post('search', [AppController::class, "search"]);
 
 Route::post('order/transection/success', [OrderController::class, "transectionsucess_app"]);
 Route::post('order/transection/failed', [OrderController::class, "transectionfailed_app"]);
 
 Route::post('branddetail',[AppController::class,"branddetail"]);
  Route::get('notification',[\App\Http\Controllers\NotificationController::class, "customerNotificationList"]);
  Route::post('notification',[\App\Http\Controllers\NotificationController::class, "customerNotificationList"]);
  Route::post('setreadnotification',[\App\Http\Controllers\NotificationController::class, "customerMarkAsRead"]);
  Route::post('relatedproductincart',[CartController::class,"relatedproductlistincart"]);
  Route::get('adminapppages',[PageController::class,"pageslist_forappview"]);
  Route::get('helpnoforapp',[PageController::class,"helpnoforapp"]);
  Route::post('gstverification', [CustomerController::class, "verifyGST"]);
  
  // Payment Webhooks & Automated Refund Hooks
  Route::post('payment/webhook', function (Request $request) {
      $refundService = app()->make(\App\Services\PaymentRefundService::class);
      return response()->json($refundService->handleWebhook($request->all(), $request->header('X-Razorpay-Signature')));
  });

  // Mobile Device FCM Token Registration
  Route::post('customer/update-fcm-token', function (Request $request) {
      $request->validate(['customer_id' => 'required', 'token' => 'required']);
      $notificationService = app()->make(\App\Services\NotificationService::class);
      $updated = $notificationService->updateDeviceToken('customer', (int)$request->customer_id, (string)$request->token);
      return response()->json(['status' => $updated, 'message' => $updated ? 'FCM token updated' : 'Customer not found']);
  });

  // Alternative Sellers Direct REST Endpoint
  Route::get('customer/orders/{id}/alternative-sellers', function ($id) {
      $altService = app()->make(\App\Services\AlternativeSellerService::class);
      $sellers = $altService->getAlternativeSellers((int)$id, 3);
      return response()->json(['status' => true, 'count' => count($sellers), 'data' => $sellers]);
  });

/*
|--------------------------------------------------------------------------
| APNI FACTORY PHASE-II (v2) CATALOGUE API ROUTES
| Centralized REST API Layer for Product Hierarchy, Dynamic Attributes,
| Variants, Seller SKU Listings, Packaging Rules, and Unified Search.
|--------------------------------------------------------------------------
*/
Route::prefix('v2')->group(function () {
    // 0. Auth
    Route::post('auth/login', [App\Http\Controllers\Api\V2\AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/user', [App\Http\Controllers\Api\V2\AuthController::class, 'user']);
        Route::post('auth/logout', [App\Http\Controllers\Api\V2\AuthController::class, 'logout']);
    });

    // 1. Categories & Subcategories (Public)
    Route::get('categories', [App\Http\Controllers\Api\V2\CategoryController::class, 'index']);
    Route::get('categories/{id}', [App\Http\Controllers\Api\V2\CategoryController::class, 'show']);
    Route::get('categories/{id}/subcategories', [App\Http\Controllers\Api\V2\CategoryController::class, 'subcategories']);
    Route::get('categories/{id}/attributes', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'categoryAttributes']);

    // 2. Brands (Public)
    Route::get('brands', [App\Http\Controllers\Api\V2\BrandController::class, 'index']);
    Route::get('brands/{id}', [App\Http\Controllers\Api\V2\BrandController::class, 'show']);
    Route::get('brands/by-category/{categoryId}', [App\Http\Controllers\Api\V2\BrandController::class, 'byCategory']);

    // 3. Products Catalogue (Public)
    Route::get('products', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'index']);
    Route::get('products/{id}', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'show']);
    Route::get('products/by-category/{categoryId}', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'byCategory']);
    Route::get('products/by-subcategory/{subcategoryId}', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'bySubcategory']);
    Route::get('products/by-brand/{brandId}', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'byBrand']);
    Route::get('products/{id}/attributes', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'productAttributes']);
    Route::get('products/{id}/variants', [App\Http\Controllers\Api\V2\ProductVariantController::class, 'productVariants']);
    Route::post('products/{id}/variants/find-by-attributes', [App\Http\Controllers\Api\V2\ProductVariantController::class, 'findByAttributes']);
    Route::get('products/{id}/skus', [App\Http\Controllers\Api\V2\SellerListingController::class, 'productSkus']);

    // 4. Variants & Listings (Public)
    Route::get('variants/{id}', [App\Http\Controllers\Api\V2\ProductVariantController::class, 'show']);
    Route::get('variants/{id}/skus', [App\Http\Controllers\Api\V2\SellerListingController::class, 'variantSkus']);
    Route::get('public/listings/{id}', [App\Http\Controllers\Api\V2\SellerListingController::class, 'publicShow']);

    // 5. Unified Catalogue Search
    Route::get('catalogue/search', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'search']);
    Route::post('attributes/validate', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'validateValues']);

    // 6. Seller SKU & Inventory Management (Authenticated Seller/Admin)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('seller/listings', [App\Http\Controllers\Api\V2\SellerListingController::class, 'sellerListings']);
        Route::get('seller/listings/{id}', [App\Http\Controllers\Api\V2\SellerListingController::class, 'show']);
        Route::get('seller/listings/{id}/stock', [App\Http\Controllers\Api\V2\SellerListingController::class, 'stockAvailability']);
        Route::post('seller/listings', [App\Http\Controllers\Api\V2\SellerListingController::class, 'store']);
        Route::put('seller/listings/{id}', [App\Http\Controllers\Api\V2\SellerListingController::class, 'update']);
        Route::patch('seller/listings/{id}/status', [App\Http\Controllers\Api\V2\SellerListingController::class, 'updateStatus']);
        Route::post('seller/listings/{id}/adjust-stock', [App\Http\Controllers\Api\V2\SellerListingController::class, 'adjustStock']);
    });

    // 7. Admin Catalogue Management (Authenticated Admin Only)
    Route::middleware(['auth:sanctum', 'admin.catalogue'])->prefix('admin')->group(function () {
        // Categories
        Route::post('categories', [App\Http\Controllers\Api\V2\CategoryController::class, 'store']);
        Route::put('categories/{id}', [App\Http\Controllers\Api\V2\CategoryController::class, 'update']);
        Route::patch('categories/{id}/status', [App\Http\Controllers\Api\V2\CategoryController::class, 'updateStatus']);

        // Brands
        Route::post('brands', [App\Http\Controllers\Api\V2\BrandController::class, 'store']);
        Route::put('brands/{id}', [App\Http\Controllers\Api\V2\BrandController::class, 'update']);
        Route::patch('brands/{id}/status', [App\Http\Controllers\Api\V2\BrandController::class, 'updateStatus']);

        // Products
        Route::post('products', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'store']);
        Route::put('products/{id}', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'update']);
        Route::patch('products/{id}/status', [App\Http\Controllers\Api\V2\ProductCatalogueController::class, 'updateStatus']);

        // Attributes Master & Category Mappings
        Route::get('attributes', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'index']);
        Route::get('attributes/{id}', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'show']);
        Route::post('attributes', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'store']);
        Route::put('attributes/{id}', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'update']);
        Route::post('attributes/{id}/options', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'storeOption']);
        Route::post('categories/{id}/attributes', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'mapToCategory']);
        Route::delete('categories/{id}/attributes/{attributeId}', [App\Http\Controllers\Api\V2\DynamicAttributeController::class, 'unmapFromCategory']);

        // Variants
        Route::post('products/{productId}/variants', [App\Http\Controllers\Api\V2\ProductVariantController::class, 'store']);
        Route::put('variants/{id}', [App\Http\Controllers\Api\V2\ProductVariantController::class, 'update']);
        Route::patch('variants/{id}/status', [App\Http\Controllers\Api\V2\ProductVariantController::class, 'updateStatus']);

        // Phase 2.5: Admin Catalogue Review APIs
        Route::get('catalogue/review-queue',                     [\App\Http\Controllers\Api\AdminCatalogueReviewApiController::class, 'queue']);
        Route::get('catalogue/review/{id}',                      [\App\Http\Controllers\Api\AdminCatalogueReviewApiController::class, 'show'])->whereNumber('id');
        Route::post('catalogue/review/{id}/approve',             [\App\Http\Controllers\Api\AdminCatalogueReviewApiController::class, 'approve'])->whereNumber('id');
        Route::post('catalogue/review/{id}/reject',              [\App\Http\Controllers\Api\AdminCatalogueReviewApiController::class, 'reject'])->whereNumber('id');
        Route::post('catalogue/review/{id}/request-corrections', [\App\Http\Controllers\Api\AdminCatalogueReviewApiController::class, 'requestCorrections'])->whereNumber('id');
        Route::get('catalogue/review/{id}/history',              [\App\Http\Controllers\Api\AdminCatalogueReviewApiController::class, 'history'])->whereNumber('id');

        // Phase 3.1: Admin Price Revision Review APIs
        Route::get('pricing/revisions/queue',                    [\App\Http\Controllers\Api\AdminPriceRevisionApiController::class, 'queue']);
        Route::get('pricing/revisions/{id}',                     [\App\Http\Controllers\Api\AdminPriceRevisionApiController::class, 'show'])->whereNumber('id');
        Route::post('pricing/revisions/{id}/approve',            [\App\Http\Controllers\Api\AdminPriceRevisionApiController::class, 'approve'])->whereNumber('id');
        Route::post('pricing/revisions/{id}/reject',             [\App\Http\Controllers\Api\AdminPriceRevisionApiController::class, 'reject'])->whereNumber('id');

        // Phase 3.2: Admin Commission & Platform Fee Management APIs
        Route::get('commissions/rules',                          [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'index']);
        Route::post('commissions/rules',                         [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'store']);
        Route::get('commissions/rules/{id}',                     [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'show'])->whereNumber('id');
        Route::post('commissions/rules/{id}/approve',            [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'approve'])->whereNumber('id');
        Route::post('commissions/rules/{id}/reject',             [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'reject'])->whereNumber('id');
        Route::post('commissions/rules/{id}/deactivate',         [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'deactivate'])->whereNumber('id');
        Route::post('commissions/preview-resolution',            [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'previewResolution']);
        Route::get('commissions/orders/{orderId}/breakdown',     [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'orderBreakdown'])->whereNumber('orderId');
        Route::get('commissions/audit-logs',                     [\App\Http\Controllers\Api\AdminCommissionRuleApiController::class, 'auditLogs']);
    });
});



// Phase 2.1: Seller Staff & Permission Management API (v2)
Route::prefix('v2/seller')->middleware('auth:sanctum')->group(function () {
    Route::get('staff',                     [\App\Http\Controllers\Api\SellerStaffController::class, 'index']);
    Route::post('staff/invite',             [\App\Http\Controllers\Api\SellerStaffController::class, 'invite']);
    Route::get('staff/{id}',                [\App\Http\Controllers\Api\SellerStaffController::class, 'show']);
    Route::patch('staff/{id}/role',         [\App\Http\Controllers\Api\SellerStaffController::class, 'assignRole']);
    Route::patch('staff/{id}/permissions',  [\App\Http\Controllers\Api\SellerStaffController::class, 'updatePermissions']);
    Route::delete('staff/{id}',             [\App\Http\Controllers\Api\SellerStaffController::class, 'deactivate']);
    Route::patch('staff/{id}/activate',     [\App\Http\Controllers\Api\SellerStaffController::class, 'activate']);
    Route::get('staff/{id}/activity',       [\App\Http\Controllers\Api\SellerStaffController::class, 'activity']);
    Route::get('permissions/matrix',        [\App\Http\Controllers\Api\SellerStaffController::class, 'permissionMatrix']);
    Route::get('roles',                     [\App\Http\Controllers\Api\SellerStaffController::class, 'roles']);

    // Phase 2.2: Seller Catalogue Excel Export APIs
    Route::get('catalogue/exports',              [\App\Http\Controllers\Api\SellerCatalogueExportApiController::class, 'index']);
    Route::post('catalogue/export',              [\App\Http\Controllers\Api\SellerCatalogueExportApiController::class, 'store']);
    Route::get('catalogue/exports/{id}',         [\App\Http\Controllers\Api\SellerCatalogueExportApiController::class, 'show']);
    Route::get('catalogue/exports/{id}/download',[\App\Http\Controllers\Api\SellerCatalogueExportApiController::class, 'download']);

    // Phase 2.3: Seller Catalogue Excel Import APIs
    Route::get('catalogue/import/template',       [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'template']);
    Route::post('catalogue/import/validate',      [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'validateUpload']);
    Route::post('catalogue/import/{id}/confirm',  [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'confirm']);
    Route::get('catalogue/import/{id}',          [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'show']);
    Route::get('catalogue/imports',              [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'index']);
    Route::get('catalogue/imports/{id}/errors',  [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'errors']);

    // Phase 2.4: Seller Catalogue Bulk Management APIs
    Route::post('catalogue/bulk/preview',       [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'preview']);
    Route::post('catalogue/bulk/{id}/confirm',  [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'confirm'])->whereNumber('id');
    Route::post('catalogue/bulk/status',        [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'statusUpdate']);
    Route::post('catalogue/bulk/fields',        [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'fieldUpdate']);
    Route::post('catalogue/bulk/archive',       [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'archive']);
    Route::post('catalogue/bulk/restore',       [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'restore']);
    Route::get('catalogue/bulk/history',        [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'history']);
    Route::get('catalogue/bulk/{id}',           [\App\Http\Controllers\Api\SellerCatalogueBulkApiController::class, 'show'])->whereNumber('id');

    // Phase 2.5: Seller Catalogue Approval Workflow APIs
    Route::post('catalogue/products/{id}/validate',          [\App\Http\Controllers\Api\SellerCatalogueApprovalApiController::class, 'validateProduct'])->whereNumber('id');
    Route::post('catalogue/products/{id}/submit',            [\App\Http\Controllers\Api\SellerCatalogueApprovalApiController::class, 'submit'])->whereNumber('id');
    Route::get('catalogue/products/{id}/submission-status',   [\App\Http\Controllers\Api\SellerCatalogueApprovalApiController::class, 'submissionStatus'])->whereNumber('id');
    Route::get('catalogue/products/{id}/review-feedback',    [\App\Http\Controllers\Api\SellerCatalogueApprovalApiController::class, 'reviewFeedback'])->whereNumber('id');
    Route::post('catalogue/products/{id}/resubmit',          [\App\Http\Controllers\Api\SellerCatalogueApprovalApiController::class, 'resubmit'])->whereNumber('id');
    Route::get('catalogue/products/{id}/review-history',     [\App\Http\Controllers\Api\SellerCatalogueApprovalApiController::class, 'reviewHistory'])->whereNumber('id');

    // Phase 2.6: Seller Catalogue Dashboard & Performance Analytics APIs
    Route::get('catalogue/analytics/dashboard',              [\App\Http\Controllers\Api\SellerCatalogueAnalyticsApiController::class, 'dashboard']);
    Route::get('catalogue/analytics/summary',                [\App\Http\Controllers\Api\SellerCatalogueAnalyticsApiController::class, 'summary']);
    Route::get('catalogue/analytics/status-distribution',    [\App\Http\Controllers\Api\SellerCatalogueAnalyticsApiController::class, 'statusDistribution']);
    Route::get('catalogue/analytics/health',                 [\App\Http\Controllers\Api\SellerCatalogueAnalyticsApiController::class, 'health']);
    Route::get('catalogue/analytics/approval-performance',   [\App\Http\Controllers\Api\SellerCatalogueAnalyticsApiController::class, 'approvalPerformance']);
    Route::get('catalogue/analytics/product-performance',    [\App\Http\Controllers\Api\SellerCatalogueAnalyticsApiController::class, 'productPerformance']);
    Route::get('catalogue/analytics/categories-brands',      [\App\Http\Controllers\Api\SellerCatalogueAnalyticsApiController::class, 'categoryBrandInsights']);

    // Phase 3.1: Dynamic Price Revision Engine (Seller APIs)
    Route::get('pricing/revisions/filter-options',           [\App\Http\Controllers\Api\PriceRevisionApiController::class, 'filterOptions']);
    Route::post('pricing/revisions/preview',                 [\App\Http\Controllers\Api\PriceRevisionApiController::class, 'preview']);
    Route::post('pricing/revisions',                         [\App\Http\Controllers\Api\PriceRevisionApiController::class, 'store']);
    Route::get('pricing/revisions',                          [\App\Http\Controllers\Api\PriceRevisionApiController::class, 'index']);
    Route::get('pricing/revisions/{id}',                     [\App\Http\Controllers\Api\PriceRevisionApiController::class, 'show'])->whereNumber('id');
    Route::post('pricing/revisions/{id}/cancel',              [\App\Http\Controllers\Api\PriceRevisionApiController::class, 'cancel'])->whereNumber('id');
    Route::get('pricing/skus/{skuId}/history',                [\App\Http\Controllers\Api\PriceRevisionApiController::class, 'skuPriceHistory'])->whereNumber('skuId');

    // Phase 3.2: Seller Commission Agreement Visibility APIs
    Route::get('commissions/agreements',                     [\App\Http\Controllers\Api\SellerCommissionApiController::class, 'agreements']);
    Route::get('commissions/orders/{orderId}/snapshot',       [\App\Http\Controllers\Api\SellerCommissionApiController::class, 'orderSnapshot'])->whereNumber('orderId');
    Route::get('commissions/skus/{skuId}/applicable-rule',    [\App\Http\Controllers\Api\SellerCommissionApiController::class, 'skuApplicableRule'])->whereNumber('skuId');

    // Phase 4.1: Seller Minimum Order Value (MOV) Configuration API
    Route::get('mov',                                         [\App\Http\Controllers\Api\SellerMovApiController::class, 'show']);
    Route::put('mov',                                         [\App\Http\Controllers\Api\SellerMovApiController::class, 'update']);

    // Phase 4.4: Seller-Isolated Ledger & Settlements APIs
    Route::get('finance/ledger',                              [\App\Http\Controllers\Api\FinancialSettlementApiController::class, 'sellerLedger']);
    Route::get('finance/settlements',                         [\App\Http\Controllers\Api\FinancialSettlementApiController::class, 'sellerSettlements']);
    Route::get('ledger',                                      [\App\Http\Controllers\Api\FinancialSettlementApiController::class, 'sellerLedger']);
    Route::get('settlements',                                 [\App\Http\Controllers\Api\FinancialSettlementApiController::class, 'sellerSettlements']);
});

// Phase 4.3: Payment Gateway Integration, Verified Callbacks & Reconciliation APIs (v2)
Route::prefix('v2/payments')->group(function () {
    Route::post('initiate',                          [\App\Http\Controllers\Api\PaymentApiController::class, 'initiate']);
    Route::get('{paymentRef}/status',                 [\App\Http\Controllers\Api\PaymentApiController::class, 'status']);
    Route::post('verify-client',                      [\App\Http\Controllers\Api\PaymentApiController::class, 'verifyClient']);
    Route::post('webhook/{gateway}',                  [\App\Http\Controllers\Api\PaymentApiController::class, 'webhook']);
});

Route::prefix('v2/finance')->group(function () {
    Route::get('payments',                             [\App\Http\Controllers\Api\FinanceReconciliationApiController::class, 'index']);
    Route::get('payments/{id}',                        [\App\Http\Controllers\Api\FinanceReconciliationApiController::class, 'show'])->whereNumber('id');
    Route::post('payments/{id}/reconcile',              [\App\Http\Controllers\Api\FinanceReconciliationApiController::class, 'reconcile'])->whereNumber('id');

    // Phase 4.4: Financial Ledger & Settlements (Admin/Finance)
    Route::get('ledger',                               [\App\Http\Controllers\Api\FinancialSettlementApiController::class, 'financeLedger']);
    Route::get('settlements',                          [\App\Http\Controllers\Api\FinancialSettlementApiController::class, 'financeSettlements']);
    Route::post('settlements/{id}/status',              [\App\Http\Controllers\Api\FinancialSettlementApiController::class, 'updateStatus'])->whereNumber('id');
});

// Phase 4.4: GST / HSN Tax Master & Calculation APIs (v2)
Route::prefix('v2/tax')->group(function () {
    Route::get('masters',                              [\App\Http\Controllers\Api\TaxMasterApiController::class, 'index']);
    Route::post('masters',                             [\App\Http\Controllers\Api\TaxMasterApiController::class, 'store']);
    Route::get('masters/{id}',                         [\App\Http\Controllers\Api\TaxMasterApiController::class, 'show'])->whereNumber('id');
    Route::post('masters/{id}/approve',                 [\App\Http\Controllers\Api\TaxMasterApiController::class, 'approve'])->whereNumber('id');
    Route::post('masters/{id}/reject',                  [\App\Http\Controllers\Api\TaxMasterApiController::class, 'reject'])->whereNumber('id');
    Route::post('calculate',                           [\App\Http\Controllers\Api\TaxMasterApiController::class, 'calculate']);
    Route::get('orders/{orderId}/snapshots',            [\App\Http\Controllers\Api\TaxMasterApiController::class, 'orderSnapshots'])->whereNumber('orderId');
});

// Phase 4.4: Invoice Generation & Lookup APIs (v2)
Route::prefix('v2/invoices')->group(function () {
    Route::get('{id}',                                 [\App\Http\Controllers\Api\InvoiceApiController::class, 'show'])->whereNumber('id');
    Route::get('orders/{orderId}',                     [\App\Http\Controllers\Api\InvoiceApiController::class, 'orderInvoices'])->whereNumber('orderId');
    Route::post('orders/{orderId}/generate',           [\App\Http\Controllers\Api\InvoiceApiController::class, 'generate'])->whereNumber('orderId');
});

// Phase 4.1: Modern Buyer Cart, Seller Grouping & Bulk Matrix APIs (v2)
Route::prefix('v2/cart')->group(function () {
    Route::get('/',                                           [\App\Http\Controllers\Api\BuyerCartApiController::class, 'index']);
    Route::post('/items',                                     [\App\Http\Controllers\Api\BuyerCartApiController::class, 'addItem']);
    Route::put('/items/{itemId}',                             [\App\Http\Controllers\Api\BuyerCartApiController::class, 'updateItem'])->whereNumber('itemId');
    Route::delete('/items/{itemId}',                          [\App\Http\Controllers\Api\BuyerCartApiController::class, 'removeItem'])->whereNumber('itemId');
    Route::delete('/',                                        [\App\Http\Controllers\Api\BuyerCartApiController::class, 'clearCart']);
    Route::post('/revalidate',                                [\App\Http\Controllers\Api\BuyerCartApiController::class, 'revalidate']);
    Route::get('/products/{productId}/variant-matrix',         [\App\Http\Controllers\Api\BuyerCartApiController::class, 'getVariantMatrix'])->whereNumber('productId');
    Route::post('/bulk-matrix',                               [\App\Http\Controllers\Api\BuyerCartApiController::class, 'submitBulkMatrix']);
});
