<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ShadeCardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\BankDetailsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProductAttributesController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaintPricingController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
///testing
use App\Console\Commands\ProcessSellerCreditNote;

Route::get('/run-process-seller', function () {
    // Instantiate the class manually and invoke the handle method
    $command = new ProcessSellerCreditNote();
    $command->handle();

    return "The credit script has run successfully! Check your database or logs.";
});
///end testing
Route::get('/livewire/livewire.js', function () {
    $path = public_path('vendor/livewire/livewire.js');
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/javascript; charset=utf-8',
        ]);
    }
    return (new \Livewire\Controllers\LivewireJavaScriptAssets)->source();
});

Route::get('/storage/app/public/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (file_exists($filePath) && is_file($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->where('path', '.*');

Route::get('/storage/{path}', function ($path) {
    if (str_starts_with($path, 'app/public/')) {
        $path = substr($path, strlen('app/public/'));
    }
    $filePath = storage_path('app/public/' . $path);
    if (file_exists($filePath) && is_file($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->where('path', '.*');

Route::post('/seller/getmidcidbybid', [Helper::class,"getmidcidbybid"]);
Route::get('/', function () {
    return view('welcome');
});
Route::post('/verify-gst', [CustomerController::class,'verifyGST_registration'])->name('verify.gst');
Route::get('/TermsAndCondition', [PageController::class, "termconditionpg_onregisterpage"]);
Route::get('invoice/order/{no}', [OrderController::class,"invoiceforappuser"]);
Route::post('/seller/getshadecardlist', [Helper::class,"getshadecardlistbycatforajax"]);
Route::post('/seller/getsubcat', [Helper::class,"getsubcatfordropdown"]);
Route::post('/seller/getbrandlist', [Helper::class,"getbrandforajax"]);
Route::post('/seller/getproductlist', [Helper::class,"getproductlistforajax"]);
Route::post('/seller/updateattributeprice', [ProductAttributesController::class,"updateattributeprice"]);
Route::get('/seller/signup', function (){  return view('seller.signup'); });
Route::post('/seller/signup', [AdminController::class,"store"])->name('sellerregistration');
////////////////////seller routes
Route::get('/seller/login', [AdminController::class,"login"]);
Route::group(['middleware' => ['auth']], function () { 
        Route::get('/seller/dashboard', [AdminController::class,"index"]);
        Route::get('/seller/profile', [CompanyController::class,"profile_sellerview"]);
        Route::post('/seller/update-image', [CompanyController::class, 'updateImage'])->name('profile.update.image');
        Route::get('/seller/brand', [BrandController::class,"index"]);
        Route::post('/seller/brand', [BrandController::class,"store"]);
        Route::post('/seller/brand/{id}', [BrandController::class,"update"])->name('brand.update');
        Route::delete('/seller/brand/{brand}', [BrandController::class,"destroy"])->name('brand.delete');
        Route::put('seller/brand/update-image', [BrandController::class, 'updateImage'])->name('seller.brand.update-image');
        
        Route::post('/seller/getmaincatandcatbybrand', [Helper::class,"getmaincatandcatbybrand"]);
        Route::get('/seller/product', [ProductController::class,"list"])->name('product.list');
        Route::get('/seller/product/bulk-upload', [ProductController::class, 'bulkUploadView'])->name('seller.product.bulk-upload');
        Route::post('/seller/product/bulk-upload', [ProductController::class, 'processBulkUpload'])->name('seller.product.bulk-upload.process');
        Route::get('/seller/product/bulk-sample-excel', [ProductController::class, 'downloadBulkSampleExcel'])->name('seller.product.bulk-sample-excel');
        Route::get('/seller/product/bulk-sample', [ProductController::class, 'downloadBulkSampleCsv'])->name('seller.product.bulk-sample');
        Route::get('/seller/product/add', [ProductController::class,"addform"]);
        Route::post('/seller/product/add', function () {
            $data['title'] = 'Add Poduct'; $data['formdata'] = $_POST;$data['formfiles'] = $_FILES;
            return view('product.addnext',$data);
        });

Route::delete('/product/filter/{id}', [ProductController::class, 'destroyFilter'])->name('product.filter.delete');
        Route::post('/seller/product/addnext', [ProductController::class,"store"])->name('product.store');
        Route::delete('/seller/product/{product}', [ProductController::class,"destroy"])->name('product.delete');
        Route::get('/seller/product/edit/{id}', [ProductController::class,"editview"])->name('product.edit');
        Route::post('/seller/product/edit/{id}', [ProductController::class,"update"]);
        Route::post('/seller/addproductattribute', [ProductAttributesController::class,"store"])->name('product.addproductattribute');
        
        // Paint & Universal Smart Pricing Routes (Product-wise and Category-wise)
        Route::get('/seller/paint-pricing', [PaintPricingController::class, 'index'])->name('seller.paint-pricing.index');
        Route::get('/seller/paint-pricing/data/{id}', [PaintPricingController::class, 'getFamilyPricingData'])->name('seller.paint-pricing.data');
        Route::post('/seller/paint-pricing/preview', [PaintPricingController::class, 'preview'])->name('seller.paint-pricing.preview');
        Route::post('/seller/paint-pricing/apply', [PaintPricingController::class, 'apply'])->name('seller.paint-pricing.apply');
        Route::post('/seller/paint-pricing/category/preview', [PaintPricingController::class, 'categoryPreview'])->name('seller.paint-pricing.category.preview');
        Route::post('/seller/paint-pricing/category/apply', [PaintPricingController::class, 'categoryApply'])->name('seller.paint-pricing.category.apply');
        Route::post('/seller/paint-pricing/sku-override', [PaintPricingController::class, 'updateSingleSku'])->name('seller.paint-pricing.sku-override');
        Route::get('/seller/paint-pricing/audit/{id}', [PaintPricingController::class, 'auditHistory'])->name('seller.paint-pricing.audit');
         
        Route::get('/seller/coupon', [CouponController::class,"index"]);
        Route::post('/seller/coupon', [CouponController::class,"store"]);
        Route::delete('/seller/coupon/{coupon}', [CouponController::class,"destroy"])->name('coupon.delete');
        
        Route::get('/seller/managecolor', [ShadeCardController::class,"index"]);
        Route::post('/seller/managecolor', [ShadeCardController::class,"sendrequesttoadmin"]);
        
        Route::get('/seller/category', [CategoryController::class,"index"]);
        Route::post('/seller/category', [CategoryController::class,"sendrequesttoadmin"]);
        
        Route::get('/seller/advertisement', [AdvertisementController::class,"index"]);
        Route::post('/seller/advertisement', [AdvertisementController::class,"store"]);
        Route::delete('/seller/advertisement/{advertisement}', [AdvertisementController::class,"destroy"])->name('advertisement.delete');
        
        Route::get('/seller/page/{name}', [PageController::class,"sellerview"])->name("seller.page");
        Route::post('/seller/page/{name}', [PageController::class,"addrupdatepage"]);
        
        Route::get('/seller/faq', [FaqController::class,"sellerview"]);
        Route::post('/seller/faq', [FaqController::class,"store"]);
        Route::delete('/seller/faq/{faq}', [FaqController::class,"destroy"])->name('faq.delete');
        
        Route::get('/seller/bank-detail', [BankDetailsController::class,"index"]);
        Route::post('/seller/bank-detail', [BankDetailsController::class,"store"]);
        
        Route::get('/seller/ticketsupport', [TicketController::class,"index"]);
        Route::post('/seller/ticketsupport', [TicketController::class,"store"]);
        Route::delete('/seller/ticketsupport/{ticketsupport}', [TicketController::class,"destroy"])->name('ticketsupport.delete');
        
        Route::get('/seller/order', [OrderController::class,"sellerorderlist"])->name('order.list'); 
        Route::get('/seller/order/{order}', [OrderController::class,"show"])->name('order.detail'); 
        Route::post('/seller/order/{order}', [OrderController::class,"update"])->name('order.update');
        Route::get('/seller/order/{order}/pdf', [OrderController::class, 'OrderInvoiceForSeller'])->name('order.orderinvoice');
        
         Route::get('/seller/help', [PageController::class,"helppageofseller"]);
        Route::get('/seller/creditnotes', [PageController::class,"creditnotesofseller"]);
        Route::get('/seller/creditdetail/{id}', [PageController::class,"creditnotes_details"])->name('creditnotes.detail');
        Route::get('/seller/transection', [PageController::class,"sellertransections"]);
        Route::get('/invoice/{id}/pdf', [OrderController::class, 'generatePdf_ofcreditnotetoseller'])->name('invoice.pdf');
        
        Route::get('/seller/citynotallow', [CompanyController::class,"restrictedcity"]);
        Route::post('/seller/citynotallow', [CompanyController::class,"update_restrictedcity"]);
        
        Route::post('/seller/profile/{id}', [CompanyController::class,"update"]);
        
        // Seller Real-Time Notifications
        Route::get('/seller/notifications', [\App\Http\Controllers\NotificationController::class, 'indexSeller'])->name('seller.notifications.index');
        Route::get('/seller/notifications/live', [\App\Http\Controllers\NotificationController::class, 'getSellerHeaderData'])->name('seller.notifications.live');
        Route::post('/seller/notifications/mark-read/{id}', [\App\Http\Controllers\NotificationController::class, 'markSellerAsRead'])->name('seller.notifications.mark-read');
        Route::post('/seller/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllSellerAsRead'])->name('seller.notifications.mark-all-read');

        // --- Seller Staff Management (Phase 2.1) ---
        Route::get('/seller/staff', [\App\Http\Controllers\SellerStaffWebController::class, 'index'])->name('seller.staff.index');
        Route::get('/seller/staff/create', [\App\Http\Controllers\SellerStaffWebController::class, 'create'])->name('seller.staff.create');
        Route::post('/seller/staff', [\App\Http\Controllers\SellerStaffWebController::class, 'store'])->name('seller.staff.store');
        Route::get('/seller/staff/{id}', [\App\Http\Controllers\SellerStaffWebController::class, 'show'])->name('seller.staff.show');
        Route::patch('/seller/staff/{id}/role', [\App\Http\Controllers\SellerStaffWebController::class, 'assignRole'])->name('seller.staff.assign-role');
        Route::patch('/seller/staff/{id}/permissions', [\App\Http\Controllers\SellerStaffWebController::class, 'updatePermissions'])->name('seller.staff.update-permissions');
        Route::patch('/seller/staff/{id}/deactivate', [\App\Http\Controllers\SellerStaffWebController::class, 'deactivate'])->name('seller.staff.deactivate');
        Route::patch('/seller/staff/{id}/activate', [\App\Http\Controllers\SellerStaffWebController::class, 'activate'])->name('seller.staff.activate');
        Route::get('/seller/staff/{id}/activity', [\App\Http\Controllers\SellerStaffWebController::class, 'activity'])->name('seller.staff.activity');

        // --- Seller Catalogue Excel Export (Phase 2.2) ---
        Route::get('/seller/catalogue/export', [\App\Http\Controllers\SellerCatalogueExportWebController::class, 'index'])->name('seller.catalogue.export');
        Route::post('/seller/catalogue/export', [\App\Http\Controllers\SellerCatalogueExportWebController::class, 'export'])->name('seller.catalogue.export.process');
        Route::get('/seller/catalogue/export/history', [\App\Http\Controllers\SellerCatalogueExportWebController::class, 'history'])->name('seller.catalogue.export.history');
        Route::get('/seller/catalogue/export/{id}/download', [\App\Http\Controllers\SellerCatalogueExportWebController::class, 'download'])->name('seller.catalogue.export.download');

        // --- Seller Catalogue Excel Import (Phase 2.3) ---
        Route::get('/seller/catalogue/import/template', [\App\Http\Controllers\SellerCatalogueImportWebController::class, 'template'])->name('seller.catalogue.import.template');
        Route::get('/seller/catalogue/import', [\App\Http\Controllers\SellerCatalogueImportWebController::class, 'index'])->name('seller.catalogue.import');
        Route::post('/seller/catalogue/import/validate', [\App\Http\Controllers\SellerCatalogueImportWebController::class, 'validateUpload'])->name('seller.catalogue.import.validate');
        Route::get('/seller/catalogue/import/{id}/preview', [\App\Http\Controllers\SellerCatalogueImportWebController::class, 'preview'])->name('seller.catalogue.import.preview');
        Route::post('/seller/catalogue/import/{id}/confirm', [\App\Http\Controllers\SellerCatalogueImportWebController::class, 'confirm'])->name('seller.catalogue.import.confirm');
        Route::get('/seller/catalogue/import/history', [\App\Http\Controllers\SellerCatalogueImportWebController::class, 'history'])->name('seller.catalogue.import.history');
        Route::get('/seller/catalogue/import/{id}/errors', [\App\Http\Controllers\SellerCatalogueImportWebController::class, 'errors'])->name('seller.catalogue.import.errors');

        // --- Seller Catalogue Bulk Management (Phase 2.4) ---
        Route::get('/seller/catalogue/bulk', [\App\Http\Controllers\SellerCatalogueBulkWebController::class, 'index'])->name('seller.catalogue.bulk');
        Route::post('/seller/catalogue/bulk/preview', [\App\Http\Controllers\SellerCatalogueBulkWebController::class, 'preview'])->name('seller.catalogue.bulk.preview');
        Route::post('/seller/catalogue/bulk/{id}/confirm', [\App\Http\Controllers\SellerCatalogueBulkWebController::class, 'confirm'])->name('seller.catalogue.bulk.confirm')->whereNumber('id');
        Route::get('/seller/catalogue/bulk/history', [\App\Http\Controllers\SellerCatalogueBulkWebController::class, 'history'])->name('seller.catalogue.bulk.history');

        // --- Seller Catalogue Approval Workflow (Phase 2.5) ---
        Route::get('/seller/catalogue/submissions', [\App\Http\Controllers\SellerCatalogueApprovalWebController::class, 'index'])->name('seller.catalogue.submissions');
        Route::get('/seller/catalogue/submissions/{id}', [\App\Http\Controllers\SellerCatalogueApprovalWebController::class, 'show'])->name('seller.catalogue.submissions.show')->whereNumber('id');
        Route::post('/seller/catalogue/submissions/{id}/validate', [\App\Http\Controllers\SellerCatalogueApprovalWebController::class, 'validateProduct'])->name('seller.catalogue.submissions.validate')->whereNumber('id');
        Route::post('/seller/catalogue/submissions/{id}/submit', [\App\Http\Controllers\SellerCatalogueApprovalWebController::class, 'submit'])->name('seller.catalogue.submissions.submit')->whereNumber('id');
        Route::post('/seller/catalogue/submissions/{id}/resubmit', [\App\Http\Controllers\SellerCatalogueApprovalWebController::class, 'resubmit'])->name('seller.catalogue.submissions.resubmit')->whereNumber('id');

        // --- Seller Catalogue Dashboard & Performance Analytics (Phase 2.6) ---
        Route::get('/seller/catalogue/analytics', [\App\Http\Controllers\SellerCatalogueAnalyticsWebController::class, 'index'])->name('seller.catalogue.analytics');

        // --- Phase 3.1: Dynamic Price Revision Engine (Seller Web Portal) ---
        Route::get('/seller/pricing/revisions',          [\App\Http\Controllers\SellerPriceRevisionWebController::class, 'index'])->name('seller.pricing.revisions.index');
        Route::get('/seller/pricing/revisions/create',   [\App\Http\Controllers\SellerPriceRevisionWebController::class, 'create'])->name('seller.pricing.revisions.create');
        Route::post('/seller/pricing/revisions/preview', [\App\Http\Controllers\SellerPriceRevisionWebController::class, 'preview'])->name('seller.pricing.revisions.preview');
        Route::post('/seller/pricing/revisions',         [\App\Http\Controllers\SellerPriceRevisionWebController::class, 'store'])->name('seller.pricing.revisions.store');
        Route::get('/seller/pricing/revisions/{id}',     [\App\Http\Controllers\SellerPriceRevisionWebController::class, 'show'])->name('seller.pricing.revisions.show')->whereNumber('id');
        Route::post('/seller/pricing/revisions/{id}/cancel', [\App\Http\Controllers\SellerPriceRevisionWebController::class, 'cancel'])->name('seller.pricing.revisions.cancel')->whereNumber('id');

        // --- Admin Catalogue Review Queue (Phase 2.5) ---
        Route::get('/admin/catalogue/reviews', [\App\Http\Controllers\AdminCatalogueReviewWebController::class, 'queue'])->name('admin.catalogue.reviews');
        Route::get('/admin/catalogue/reviews/{id}', [\App\Http\Controllers\AdminCatalogueReviewWebController::class, 'show'])->name('admin.catalogue.reviews.show')->whereNumber('id');
        Route::post('/admin/catalogue/reviews/{id}/approve', [\App\Http\Controllers\AdminCatalogueReviewWebController::class, 'approve'])->name('admin.catalogue.reviews.approve')->whereNumber('id');
        Route::post('/admin/catalogue/reviews/{id}/reject', [\App\Http\Controllers\AdminCatalogueReviewWebController::class, 'reject'])->name('admin.catalogue.reviews.reject')->whereNumber('id');
        Route::post('/admin/catalogue/reviews/{id}/request-corrections', [\App\Http\Controllers\AdminCatalogueReviewWebController::class, 'requestCorrections'])->name('admin.catalogue.reviews.request-corrections')->whereNumber('id');

        // --- Phase 3.1: Admin Price Revision Review Queue (Admin Web Portal) ---
        Route::get('/admin/pricing/revisions',           [\App\Http\Controllers\AdminPriceRevisionWebController::class, 'queue'])->name('admin.pricing.revisions.queue');
        Route::get('/admin/pricing/revisions/{id}',      [\App\Http\Controllers\AdminPriceRevisionWebController::class, 'show'])->name('admin.pricing.revisions.show')->whereNumber('id');
        Route::post('/admin/pricing/revisions/{id}/approve', [\App\Http\Controllers\AdminPriceRevisionWebController::class, 'approve'])->name('admin.pricing.revisions.approve')->whereNumber('id');
        Route::post('/admin/pricing/revisions/{id}/reject',  [\App\Http\Controllers\AdminPriceRevisionWebController::class, 'reject'])->name('admin.pricing.revisions.reject')->whereNumber('id');

        // --- Phase 3.2: Seller Commission Agreement Visibility ---
        Route::get('/seller/commissions/agreements',     [\App\Http\Controllers\SellerCommissionWebController::class, 'agreements'])->name('seller.commissions.agreements');
        Route::get('/seller/commissions/orders/{id}',    [\App\Http\Controllers\SellerCommissionWebController::class, 'orderSnapshot'])->name('seller.commissions.orders.show')->whereNumber('id');

        // --- Phase 4.1: Seller Minimum Order Value (MOV) Configuration ---
        Route::get('/seller/settings/mov',               [\App\Http\Controllers\SellerMovWebController::class, 'index'])->name('seller.settings.mov.index');
        Route::post('/seller/settings/mov',              [\App\Http\Controllers\SellerMovWebController::class, 'update'])->name('seller.settings.mov.update');

        // --- Phase 3.2: Admin Commission & Platform Fee Engine ---
        Route::get('/admin/commissions/rules',           [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'index'])->name('admin.commissions.rules.index');
        Route::get('/admin/commissions/rules/create',    [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'create'])->name('admin.commissions.rules.create');
        Route::post('/admin/commissions/rules',          [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'store'])->name('admin.commissions.rules.store');
        Route::get('/admin/commissions/rules/{id}',      [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'show'])->name('admin.commissions.rules.show')->whereNumber('id');
        Route::post('/admin/commissions/rules/{id}/approve', [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'approve'])->name('admin.commissions.rules.approve')->whereNumber('id');
        Route::post('/admin/commissions/rules/{id}/reject',  [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'reject'])->name('admin.commissions.rules.reject')->whereNumber('id');
        Route::post('/admin/commissions/rules/{id}/deactivate', [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'deactivate'])->name('admin.commissions.rules.deactivate')->whereNumber('id');
        Route::get('/admin/commissions/orders/{id}',     [\App\Http\Controllers\AdminCommissionRuleWebController::class, 'orderBreakdown'])->name('admin.commissions.orders.show')->whereNumber('id');

});

// Customer Mobile App Simulator & Experience Preview
Route::get('/customer/app-preview', [\App\Http\Controllers\AppPreviewController::class, 'index'])->name('customer.app-preview');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
