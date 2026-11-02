<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BulkProductSpreadsheetService;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ShadeCard;
use App\Models\BoxPacking;

echo "=== IMPORTING LIVE 30-PRODUCT DATASET INTO LOCAL DATABASE ===\n";

$service = app(BulkProductSpreadsheetService::class);
$xlsxPath = base_path('apnifactory_products_bulk_template.xlsx');

$uploadedFile = new \Illuminate\Http\UploadedFile(
    $xlsxPath,
    'apnifactory_products_bulk_template.xlsx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    null,
    true
);

$res = $service->importSpreadsheet($uploadedFile, [], null, 3);
echo "Import Result:\n";
print_r($res);

$productCount = Product::where('user_id', 3)->count();
$variantCount = ProductAttributes::whereIn('product_id', Product::where('user_id', 3)->pluck('id'))->count();
echo "Total Products for Seller (ID 3): $productCount\n";
echo "Total Product Variants for Seller: $variantCount\n";

echo "=== IMPORT COMPLETED SUCCESSFULLY ===\n";

