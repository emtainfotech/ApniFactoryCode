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
 Route::post('sendotp', [CustomerController::class, "sendotp"]);
 Route::post('verifyotp', [CustomerController::class, "verifyotp"]);
 Route::post('resetpassword', [CustomerController::class, "resetpassword"]);
 Route::get('statecitypincode', [AppController::class, "statecitypincode"]);
 /////homescreen
 Route::get('homescreen', [AppController::class, "homescreen"]);
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
 
 Route::post('search', [AppController::class, "search"]);
 
 Route::post('order/transection/success', [OrderController::class, "transectionsucess_app"]);
 Route::post('order/transection/failed', [OrderController::class, "transectionfailed_app"]);
 
 Route::post('branddetail',[AppController::class,"branddetail"]);
  Route::get('notification',[AppController::class,"notificationlist"]);
  Route::post('setreadnotification',[AppController::class,"notificationreadbyuser"]);
  Route::post('relatedproductincart',[CartController::class,"relatedproductlistincart"]);
  Route::get('adminapppages',[PageController::class,"pageslist_forappview"]);
  Route::get('helpnoforapp',[PageController::class,"helpnoforapp"]);
  Route::post('gstverification', [CustomerController::class, "verifyGST"]);
 
 
 
 
 
 
 
 
 
 
 
 