<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductAttributes;

$products = Product::all();
echo "Total products in database: " . $products->count() . "\n\n";

$toRemove = [];
$toKeep = [];

foreach ($products as $p) {
    $img = trim($p->image ?? '');
    $isDefaultOrEmpty = false;
    $reason = '';

    if (empty($img)) {
        $isDefaultOrEmpty = true;
        $reason = 'EMPTY_IMAGE';
    } elseif ($img === 'default.png' || $img === 'product/default.png') {
        $isDefaultOrEmpty = true;
        $reason = 'DEFAULT_APNI_FACTORY_LOGO';
    } elseif (strpos(strtolower($img), 'apni-factory') !== false || strpos(strtolower($img), 'apnifactory') !== false) {
        $isDefaultOrEmpty = true;
        $reason = 'APNI_FACTORY_LOGO_IN_NAME';
    } else {
        // Check if file exists on disk in public/storage
        $filePath1 = public_path('storage/' . $img);
        $filePath2 = storage_path('app/public/' . $img);
        if (!file_exists($filePath1) && !file_exists($filePath2)) {
            $isDefaultOrEmpty = true;
            $reason = 'IMAGE_FILE_NOT_FOUND_ON_DISK';
        }
    }

    if ($isDefaultOrEmpty) {
        $toRemove[] = [
            'id' => $p->id,
            'name' => $p->name,
            'image' => $img,
            'reason' => $reason
        ];
    } else {
        $toKeep[] = [
            'id' => $p->id,
            'name' => $p->name,
            'image' => $img
        ];
    }
}

echo "=== PRODUCTS TO REMOVE (" . count($toRemove) . ") ===\n";
foreach ($toRemove as $item) {
    echo "ID: {$item['id']} | Name: {$item['name']} | Img: {$item['image']} | Reason: {$item['reason']}\n";
}

echo "\n=== PRODUCTS TO KEEP (" . count($toKeep) . ") ===\n";
foreach ($toKeep as $item) {
    echo "ID: {$item['id']} | Name: {$item['name']} | Img: {$item['image']}\n";
}
