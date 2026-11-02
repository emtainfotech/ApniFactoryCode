<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\BulkProductSpreadsheetService::class);
$excelPath = $service->generateSampleExcel(3);

copy($excelPath, "C:/Users/EPC001/Desktop/ApniFactoryBackend/apnifactory_products_bulk_template.xlsx");
copy($excelPath, "C:/Users/EPC001/Desktop/apni_factory/apni_factory/apnifactory_products_bulk_template.xlsx");
copy($excelPath, "C:/Users/EPC001/.gemini/antigravity/brain/c7411eb4-6f80-4611-985e-d57fbe51aac3/apnifactory_products_bulk_template.xlsx");

$csvPath = $service->generateSampleCsv();
copy($csvPath, "C:/Users/EPC001/Desktop/ApniFactoryBackend/apnifactory_products_bulk_sample.csv");
copy($csvPath, "C:/Users/EPC001/Desktop/apni_factory/apni_factory/apnifactory_products_bulk_sample.csv");
copy($csvPath, "C:/Users/EPC001/.gemini/antigravity/brain/c7411eb4-6f80-4611-985e-d57fbe51aac3/apnifactory_products_bulk_sample.csv");

echo "Sample Excel and CSV generated and saved in all target locations successfully!\n";

