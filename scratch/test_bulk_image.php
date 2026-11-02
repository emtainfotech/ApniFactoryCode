<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BulkProductSpreadsheetService;
use App\Models\Product;

echo "=== TESTING IMAGE IMPORT WITH BULK SERVICE ===\n";

$sourcePng = 'C:/Users/EPC001/Desktop/ApniFactoryBackend/backup/panel/tr/public/apni-factory-admin/img/avatar-2.png';
$dummyImagePath = storage_path('app/temp_test_paint.png');
copy($sourcePng, $dummyImagePath);

echo "1. Created dummy image from source at: $dummyImagePath\n";

$companionImageFile = new \Illuminate\Http\UploadedFile(
    $dummyImagePath,
    'test_gloss_red.png',
    'image/png',
    null,
    true
);

// Create a small CSV referencing test_gloss_red.png
$csvPath = storage_path('app/temp_test_with_img.csv');
$fp = fopen($csvPath, 'w');
fputcsv($fp, [
    'category_name', 'brand_name', 'product_name', 'title', 'description', 
    'hsn_code', 'tax_percent', 'box_packing', 'shade_or_color', 'shade_hexcode', 
    'seller_price', 'customer_mrp', 'image_url_or_filename'
]);
fputcsv($fp, [
    'Enamel Paints', 'Asian Paints Pro', 'Gloss Synthetic Red Enamel', 'High Gloss Red', 
    'Premium Red Paint', '3208', '18', '4 Litres', 'Crimson Red', '#DC3232', 
    '1250.00', '1600.00', 'test_gloss_red.png'
]);
fclose($fp);

$uploadedCsv = new \Illuminate\Http\UploadedFile(
    $csvPath,
    'test_products.csv',
    'text/csv',
    null,
    true
);

$service = app(BulkProductSpreadsheetService::class);
$res = $service->importSpreadsheet($uploadedCsv, [$companionImageFile], null, 3);
echo "2. Import result:\n";
print_r($res);

$product = Product::where('user_id', 3)->where('name', 'Gloss Synthetic Red Enamel')->first();
if ($product) {
    echo "3. Verified Product in DB: ID {$product->id}, Image: {$product->image}, MultipleImages: {$product->multipleimages}\n";
    $fullPath = public_path('storage/' . $product->image);
    echo "   Image file on disk exists: " . (file_exists($fullPath) ? 'YES (' . filesize($fullPath) . ' bytes)' : 'NO') . "\n";
} else {
    echo "Product not found!\n";
}

@unlink($dummyImagePath);
@unlink($csvPath);
echo "=== TEST COMPLETED SUCCESSFULLY ===\n";
