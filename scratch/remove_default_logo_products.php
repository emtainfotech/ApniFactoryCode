<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ProductVolumeSlab;
use Illuminate\Support\Facades\DB;

$removeIds = [];
$products = Product::all();

foreach ($products as $p) {
    $img = trim($p->image ?? '');
    $isDefaultOrEmpty = false;

    if (empty($img)) {
        $isDefaultOrEmpty = true;
    } elseif ($img === 'default.png' || $img === 'product/default.png') {
        $isDefaultOrEmpty = true;
    } elseif (strpos(strtolower($img), 'apni-factory') !== false || strpos(strtolower($img), 'apnifactory') !== false) {
        $isDefaultOrEmpty = true;
    } else {
        $filePath1 = public_path('storage/' . $img);
        $filePath2 = storage_path('app/public/' . $img);
        if (!file_exists($filePath1) && !file_exists($filePath2)) {
            $isDefaultOrEmpty = true;
        }
    }

    if ($isDefaultOrEmpty) {
        $removeIds[] = $p->id;
    }
}

echo "Found " . count($removeIds) . " products to remove.\n";

if (!empty($removeIds)) {
    DB::beginTransaction();
    try {
        // Delete related attributes
        $deletedAttrs = ProductAttributes::whereIn('product_id', $removeIds)->delete();
        echo "Deleted {$deletedAttrs} related product_attributes.\n";

        // Delete volume slabs if any
        if (\Illuminate\Support\Facades\Schema::hasTable('product_volume_slabs')) {
            $deletedSlabs = ProductVolumeSlab::whereIn('product_id', $removeIds)->delete();
            echo "Deleted {$deletedSlabs} related product_volume_slabs.\n";
        }

        // Delete cart items referencing these products
        if (\Illuminate\Support\Facades\Schema::hasTable('carts')) {
            $deletedCarts = DB::table('carts')->whereIn('product_id', $removeIds)->delete();
            echo "Deleted {$deletedCarts} related cart entries.\n";
        }

        // Delete favorite items referencing these products
        if (\Illuminate\Support\Facades\Schema::hasTable('favorites')) {
            $deletedFavs = DB::table('favorites')->whereIn('product_id', $removeIds)->delete();
            echo "Deleted {$deletedFavs} related favorites.\n";
        }

        // Delete products
        $deletedProducts = Product::whereIn('id', $removeIds)->delete();
        echo "Deleted {$deletedProducts} products from products table.\n";

        DB::commit();
        echo "Successfully committed deletions!\n";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "ERROR during deletion: " . $e->getMessage() . "\n";
    }
}

// Show remaining products
$remaining = Product::all();
echo "\nRemaining active products count: " . $remaining->count() . "\n";
foreach ($remaining as $rp) {
    echo "ID: {$rp->id} | {$rp->name} | Img: {$rp->image}\n";
}
