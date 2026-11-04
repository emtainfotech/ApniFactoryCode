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
    Route::post('catalogue/import/{id}/confirm',  [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'confirm'])->whereNumber('id');
    Route::get('catalogue/import/{id}',          [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'show'])->whereNumber('id');
    Route::get('catalogue/imports',              [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'index']);
    Route::get('catalogue/imports/{id}/errors',  [\App\Http\Controllers\Api\SellerCatalogueImportApiController::class, 'errors'])->whereNumber('id');
});
