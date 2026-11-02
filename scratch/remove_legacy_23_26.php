<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductAttributes;
use Illuminate\Support\Facades\DB;

$legacyIds = [23, 26];

ProductAttributes::whereIn('product_id', $legacyIds)->delete();
DB::table('cart')->whereIn('product_id', $legacyIds)->delete();
Product::whereIn('id', $legacyIds)->delete();

echo "Removed legacy products 23 and 26.\n";
echo "Active products count: " . Product::count() . "\n";

