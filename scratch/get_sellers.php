<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Product;

echo "=== APNIFACTORY: DEMO SELLER ACCOUNTS & THEIR PRODUCTS ===\n\n";

$users = User::all();
foreach ($users as $u) {
    $company = Company::where('user_id', $u->id)->first();
    $products = Product::where('user_id', $u->id)->get(['id', 'name', 'category_id', 'status']);
    
    echo "Seller User ID: " . $u->id . "\n";
    echo "Seller Name:    " . $u->name . "\n";
    echo "Email:          " . $u->email . "\n";
    echo "Company Name:   " . ($company ? $company->name : 'N/A') . "\n";
    echo "Company ID:     " . ($company ? $company->id : 'N/A') . "\n";
    echo "Total Products: " . $products->count() . "\n";
    
    if ($products->count() > 0) {
        echo "Products List:\n";
        foreach ($products as $p) {
            echo "  * [ID: {$p->id}] {$p->name} (Status: {$p->status})\n";
        }
    }
    echo "--------------------------------------------------------\n\n";
}
