<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Brand;
use App\Models\Product;

$user = User::where('email', 'seller@apnifactory.local')->first();
echo "User ID: " . $user->id . "\n";
echo "User Name: " . $user->name . "\n";
echo "User Email: " . $user->email . "\n";

$company = Company::where('user_id', $user->id)->first();
echo "Company ID: " . ($company ? $company->id : 'NONE') . "\n";
echo "Company Name: " . ($company ? $company->name : 'NONE') . "\n";

$brands = Brand::where('user_id', $user->id)->get();
echo "Brands count: " . $brands->count() . "\n";
foreach ($brands as $b) {
    echo " - Brand: {$b->name} (status: {$b->status}, adminresponse: {$b->adminresponse})\n";
}

$products = Product::where('user_id', $user->id)->get();
echo "Products count: " . $products->count() . "\n";
foreach ($products as $p) {
    echo " - Product: {$p->name} (category_id: {$p->category_id}, status: {$p->status})\n";
}

