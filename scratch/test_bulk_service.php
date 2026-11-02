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

echo "=== TESTING BULK SPREADSHEET SERVICE ===\n";

$service = app(BulkProductSpreadsheetService::class);

// 1. Test Excel Generation
echo "1. Generating Sample Excel...\n";
$excelPath = $service->generateSampleExcel(3);
echo "   Sample Excel generated at: $excelPath, size: " . filesize($excelPath) . " bytes\n";

// 2. Test CSV Generation
echo "2. Generating Sample CSV...\n";
$csvPath = $service->generateSampleCsv();
echo "   Sample CSV generated at: $csvPath, size: " . filesize($csvPath) . " bytes\n";

// 3. Test Importing the Generated Sample Excel!
echo "3. Testing import of generated sample Excel...\n";
$uploadedFile = new \Illuminate\Http\UploadedFile(
    $excelPath,
    'sample_test.xlsx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    null,
    true
);

$res = $service->importSpreadsheet($uploadedFile, [], null, 3);
echo "   Import Result:\n";
print_r($res);

// 4. Verify in DB
echo "4. Verifying created products in DB:\n";
$product = Product::where('user_id', 3)->where('name', 'Apcolite Premium Gloss Enamel')->first();
if ($product) {
    echo "   Product Found: ID: {$product->id}, Name: {$product->name}, Image: {$product->image}\n";
    $attributes = ProductAttributes::where('product_id', $product->id)->get();
    echo "   Variants Count: " . count($attributes) . "\n";
    foreach ($attributes as $attr) {
        $shadeName = ShadeCard::where('id', $attr->color)->value('name') ?: $attr->color;
        $packName  = BoxPacking::where('id', $attr->quantity)->value('name') ?: $attr->quantity;
        echo "     - Size: {$packName} | Shade: {$shadeName} | Seller Base: Rs.{$attr->seller_price} | Customer Wholesale: Rs.{$attr->price} | MRP: Rs.{$attr->oldprice}\n";
    }
} else {
    echo "   Product not found!\n";
}

@unlink($excelPath);
@unlink($csvPath);
echo "=== TEST COMPLETED SUCCESSFULLY ===\n";

