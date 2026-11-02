<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== APNIFACTORY: DEEP COMPONENT & CODE-BLOCK REVIEW ===\n\n";

// 1. Check Admin user
$adminUser = User::where('email', 'admin@apnifactory.local')->first();
echo "Admin User: " . ($adminUser ? "Found (ID {$adminUser->id})" : "NOT FOUND") . "\n";

// 2. Check Orders and Order Details
$ordersCount = Order::count();
$orderDetailCount = \DB::table('orderdetail')->count();
$orderStatusCount = \DB::table('order_status')->count();
echo "Total Orders: {$ordersCount} | Order Details: {$orderDetailCount} | Order Status Rows: {$orderStatusCount}\n";

// 3. Check Products and Attributes
$productsCount = Product::count();
$attributesCount = \DB::table('product_attributes')->count();
echo "Total Products: {$productsCount} | Total Variant SKUs (product_attributes): {$attributesCount}\n";

// 4. Check Companies and Users
$companiesCount = Company::count();
$customersCount = Customer::count();
$brandsCount = Brand::count();
echo "Total Companies: {$companiesCount} | Total Customers: {$customersCount} | Total Brands: {$brandsCount}\n\n";

// 5. Test Query Times on critical operations
$t0 = microtime(true);
$latestOrders = Order::with(['user', 'customer'])->latest()->limit(25)->get();
foreach ($latestOrders as $o) {
    $st = \DB::table('order_status')->where('order_no', $o->orderno)->latest('id')->value('status');
}
$tOrderFetch = round((microtime(true) - $t0) * 1000, 2);
echo "Order Table 25-row rendering simulation time: {$tOrderFetch} ms\n";

$t1 = microtime(true);
$pincodeStates = \DB::table('india_pincode')->groupBy('state')->pluck('state');
$tPincode = round((microtime(true) - $t1) * 1000, 2);
echo "India Pincode unindexed groupBy('state') query time: {$tPincode} ms\n";

$t2 = microtime(true);
$productAttrFetch = \DB::table('product_attributes')->where('product_id', 5)->get();
$tAttr = round((microtime(true) - $t2) * 1000, 2);
echo "Product Attributes fetch for Product #5 time: {$tAttr} ms\n";

echo "\nSystem Check Completed Successfully.\n";

