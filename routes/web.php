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

});

// Customer Mobile App Simulator & Experience Preview
Route::get('/customer/app-preview', [\App\Http\Controllers\AppPreviewController::class, 'index'])->name('customer.app-preview');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
