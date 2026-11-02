<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductAttributes;

echo "=== CHECKING CART & CARTATTRIBUTE TABLES ===\n";

$validProductIds = Product::pluck('id')->toArray();
$validAttrIds = ProductAttributes::pluck('id')->toArray();

// Find orphaned carts
$orphanedCarts = DB::table('cart')->whereNotIn('product_id', $validProductIds)->get();
echo "Found " . $orphanedCarts->count() . " orphaned cart items.\n";

foreach ($orphanedCarts as $oc) {
    echo "  Deleting cart ID: {$oc->id} with invalid product_id: {$oc->product_id}\n";
    DB::table('cartattribute')->where('cart_id', $oc->id)->delete();
    DB::table('cart')->where('id', $oc->id)->delete();
}

// Find orphaned cartattributes
$orphanedCartAttrs = DB::table('cartattribute')->whereNotIn('product_attributes_id', $validAttrIds)->get();
echo "Found " . $orphanedCartAttrs->count() . " orphaned cartattribute items.\n";
foreach ($orphanedCartAttrs as $oca) {
    echo "  Deleting cartattribute ID: {$oca->id} with invalid attribute_id: {$oca->product_attributes_id}\n";
    DB::table('cartattribute')->where('id', $oca->id)->delete();
}

// Check remaining
$remainingCarts = DB::table('cart')->get();
echo "\nRemaining valid cart rows: " . $remainingCarts->count() . "\n";
foreach ($remainingCarts as $rc) {
    $p = Product::find($rc->product_id);
    echo "  Cart ID {$rc->id} | Product: " . ($p ? $p->name : 'MISSING') . "\n";
}
