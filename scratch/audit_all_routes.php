<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Login as Demo Seller
$seller = User::where('email', 'seller@apnifactory.local')->first();
if ($seller) {
    Auth::login($seller);
}

$routesToTest = [
    // Seller Panel Routes
    'Seller Dashboard'        => '/seller/dashboard',
    'Seller Profile'          => '/seller/profile',
    'Seller Brand List'       => '/seller/brand',
    'Seller Category List'    => '/seller/category',
    'Seller Product List'     => '/seller/product',
    'Seller Add Product Form' => '/seller/addproduct',
    'Seller Smart Pricing'    => '/seller/paint-pricing',
    'Seller Orders'           => '/seller/order',
    'Seller Credit Notes'     => '/seller/creditnotes',
    'Seller Wallet'           => '/seller/wallet',
    'Seller Bank Details'     => '/seller/bankdetail',
    'Seller Support Tickets'  => '/seller/ticketsupport',
    'Seller Notifications'    => '/seller/notifications',
    'Seller Restricted City'  => '/seller/citynotallow',
    'Seller Coupons'          => '/seller/coupon',
    'Seller Advertisements'   => '/seller/advertisement',
    'Seller FAQs'             => '/seller/faq',
    'Seller Help'             => '/seller/help',

    // Customer Simulator
    'Customer App Simulator'  => '/customer/app-preview',

    // Filament Admin Routes
    'Admin Login'             => '/admin/login',
    'Admin Dashboard'         => '/admin',
    'Admin Orders'            => '/admin/orders',
    'Admin Products'          => '/admin/products',
    'Admin Companies'         => '/admin/companies',
    'Admin Customers'         => '/admin/customers',
    'Admin Brands'            => '/admin/brands',
    'Admin Wallets'           => '/admin/wallets',
    'Admin Transactions'      => '/admin/transections',
    'Admin Categories'        => '/admin/categories',
    'Admin Box Packings'      => '/admin/box-packings',
    'Admin Shade Cards'       => '/admin/shade-cards',
    'Admin Coupons'           => '/admin/coupons',
    'Admin Advertisements'    => '/admin/advertisements',
    'Admin Sliders'           => '/admin/sliders',
    'Admin FAQs'              => '/admin/faqs',
    'Admin Feedback'          => '/admin/feedback',
    'Admin Tickets'           => '/admin/tickets',
    'Admin Pages'             => '/admin/pages',
];

echo "====================================================================================================\n";
echo "                         APNIFACTORY: AUTOMATED ROUTE AUDIT & HEALTH CHECK\n";
echo "====================================================================================================\n\n";

$results = [];

foreach ($routesToTest as $name => $uri) {
    DB::enableQueryLog();
    $startTime = microtime(true);

    try {
        $request = Request::create($uri, 'GET');
        if ($seller) {
            $request->setUserResolver(fn () => $seller);
        }

        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        $results[] = [
            'name'     => $name,
            'uri'      => $uri,
            'status'   => $status,
            'time_ms'  => $durationMs,
            'queries'  => $queryCount,
            'error'    => null,
        ];

        $icon = ($status >= 200 && $status < 400) ? '✅' : '⚠️';
        echo sprintf("%-28s | URI: %-26s | Status: %3d | Time: %7.2f ms | DB Queries: %3d %s\n",
            $name, $uri, $status, $durationMs, $queryCount, $icon);

    } catch (\Throwable $e) {
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $results[] = [
            'name'     => $name,
            'uri'      => $uri,
            'status'   => 500,
            'time_ms'  => $durationMs,
            'queries'  => 0,
            'error'    => $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . basename($e->getFile()),
        ];
        echo sprintf("%-28s | URI: %-26s | Status: 500 | Error: %s ❌\n",
            $name, $uri, $e->getMessage());
    }

    DB::disableQueryLog();
    DB::flushQueryLog();
}

echo "\n====================================================================================================\n";
echo "                                   SLOWEST ROUTES / HEAVY DB QUERIES\n";
echo "====================================================================================================\n";

usort($results, fn($a, $b) => $b['queries'] <=> $a['queries']);
foreach (array_slice($results, 0, 10) as $sr) {
    echo sprintf("- %-28s (%-25s): %3d queries, %7.2f ms\n", $sr['name'], $sr['uri'], $sr['queries'], $sr['time_ms']);
}

