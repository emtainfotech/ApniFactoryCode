<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\BoxPacking;
use App\Models\ShadeCard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BulkProductSpreadsheetService
{
    protected $pricingService;

    public function __construct(PaintPricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Generate a multi-sheet Excel (.xlsx) sample template containing live system data
     */
    public function generateSampleExcel($userId = null)
    {
        $userId = $userId ?: (Auth::check() ? Auth::id() : 1);
        $company = Company::where('user_id', $userId)->first();
        $companyId = $company ? $company->id : 1;

        $categories = Category::whereIn('status', ['Active', '1', 1])->orderBy('name', 'asc')->get();
        $brands     = Brand::where('user_id', $userId)->whereIn('status', ['Active', '1', 1])->orderBy('name', 'asc')->get();
        if ($brands->isEmpty()) {
            $brands = Brand::whereIn('status', ['Active', '1', 1])->take(10)->get();
        }
        $boxPackings = BoxPacking::whereIn('status', ['Active', '1', 1])->orderBy('id', 'asc')->get();
        $shadeCards  = ShadeCard::whereIn('status', ['Active', '1', 1])->orderBy('id', 'asc')->get();

        $tempPath = storage_path('app/temp_bulk_sample_' . uniqid() . '.xlsx');
        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create temporary Excel file.");
        }

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet4.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');

        // _rels/.rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>
  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet4.xml"/>
</Relationships>');

        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Products_Template" sheetId="1" r:id="rId1"/>
    <sheet name="Available_Shades" sheetId="2" r:id="rId2"/>
    <sheet name="Available_Sizes" sheetId="3" r:id="rId3"/>
    <sheet name="Available_Brands" sheetId="4" r:id="rId4"/>
  </sheets>
</workbook>');

        // Sheet 1: Products_Template
        $headers1 = [
            'category_name',
            'brand_name',
            'product_name',
            'title',
            'description',
            'hsn_code',
            'tax_percent',
            'box_packing',
            'shade_or_color',
            'shade_hexcode',
            'seller_price',
            'customer_mrp',
            'image_url_or_filename'
        ];

        $sampleRows1 = [
            // Product 1: Multiple sizes & shades
            [
                'Enamel Paints',
                $brands->first() ? $brands->first()->name : 'Asian Paints Pro',
                'Apcolite Premium Gloss Enamel',
                'Apcolite Premium High-Gloss Synthetic Enamel',
                'Premium quality synthetic gloss enamel with mirror-like shine and rust-inhibitor formula.',
                '3208',
                '18',
                '1 Litre',
                'Super White',
                '#FFFFFF',
                '380.00',
                '490.00',
                'apcolite_white.jpg'
            ],
            [
                'Enamel Paints',
                $brands->first() ? $brands->first()->name : 'Asian Paints Pro',
                'Apcolite Premium Gloss Enamel',
                'Apcolite Premium High-Gloss Synthetic Enamel',
                'Premium quality synthetic gloss enamel with mirror-like shine and rust-inhibitor formula.',
                '3208',
                '18',
                '4 Litres',
                'Super White',
                '#FFFFFF',
                '1480.00',
                '1890.00',
                'apcolite_white_4l.jpg'
            ],
            [
                'Enamel Paints',
                $brands->first() ? $brands->first()->name : 'Asian Paints Pro',
                'Apcolite Premium Gloss Enamel',
                'Apcolite Premium High-Gloss Synthetic Enamel',
                'Premium quality synthetic gloss enamel with mirror-like shine and rust-inhibitor formula.',
                '3208',
                '18',
                '1 Litre',
                'Royal Blue',
                '#1E3A8A',
                '410.00',
                '530.00',
                'apcolite_blue.jpg'
            ],
            [
                'Enamel Paints',
                $brands->first() ? $brands->first()->name : 'Asian Paints Pro',
                'Apcolite Premium Gloss Enamel',
                'Apcolite Premium High-Gloss Synthetic Enamel',
                'Premium quality synthetic gloss enamel with mirror-like shine and rust-inhibitor formula.',
                '3208',
                '18',
                '4 Litres',
                'Royal Blue',
                '#1E3A8A',
                '1590.00',
                '2050.00',
                'apcolite_blue_4l.jpg'
            ],
            // Product 2: Cotton Fabric wholesale roll
            [
                'Cotton Fabrics',
                'Vardhman Textiles',
                'Organic Combed Cambric Cotton 60s',
                'Fine Cambric Combed Cotton Fabric Roll',
                '100% fine combed cotton weave for luxury apparel and wholesale dressmaking.',
                '5208',
                '5',
                '25 Meters',
                'Optic White',
                '#F8F9FA',
                '2400.00',
                '3000.00',
                'cotton_cambric.jpg'
            ],
            // Product 3: Men Formal Shirt
            [
                'Men Wear',
                'Raymond Apparel',
                'Executive Slim-Fit Cotton Formal Shirt',
                'Slim-Fit Oxford Button Down Shirt',
                'Tailored cotton executive formal shirt with wrinkle-free finish.',
                '6205',
                '12',
                'Box of 12 (Assorted S-XL)',
                'Navy Blue',
                '#001F3F',
                '7200.00',
                '9600.00',
                'raymond_navy_box.jpg'
            ]
        ];

        $sheet1Xml = $this->buildSheetXml($headers1, $sampleRows1);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);

        // Sheet 2: Available Shades
        $headers2 = ['Shade ID', 'Shade Name', 'Hex Code', 'Preview'];
        $rows2 = [];
        foreach ($shadeCards as $sc) {
            $rows2[] = [$sc->id, $sc->name, $sc->hexcode, $sc->hexcode];
        }
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->buildSheetXml($headers2, $rows2));

        // Sheet 3: Available Sizes & Packings
        $headers3 = ['Size/Packing ID', 'Packing Name', 'Pieces Count'];
        $rows3 = [];
        foreach ($boxPackings as $bp) {
            $rows3[] = [$bp->id, $bp->name, $bp->pcs];
        }
        $zip->addFromString('xl/worksheets/sheet3.xml', $this->buildSheetXml($headers3, $rows3));

        // Sheet 4: Available Brands & Categories
        $headers4 = ['Brand Name', 'Category Name', 'Company'];
        $rows4 = [];
        foreach ($brands as $b) {
            $catName = Category::where('id', $b->category_id)->value('name') ?: 'General';
            $rows4[] = [$b->name, $catName, $company ? $company->name : ''];
        }
        $zip->addFromString('xl/worksheets/sheet4.xml', $this->buildSheetXml($headers4, $rows4));

        $zip->close();
        return $tempPath;
    }

    /**
     * Generate a standard UTF-8 CSV sample template
     */
    public function generateSampleCsv()
    {
        $headers = [
            'category_name',
            'brand_name',
            'product_name',
            'title',
            'description',
            'hsn_code',
            'tax_percent',
            'box_packing',
            'shade_or_color',
            'shade_hexcode',
            'seller_price',
            'customer_mrp',
            'image_url_or_filename'
        ];

        $rows = [
            [
                'Enamel Paints',
                'Asian Paints Pro',
                'Apcolite Premium Gloss Enamel',
                'Apcolite Premium High-Gloss Synthetic Enamel',
                'Premium synthetic gloss enamel with mirror-like shine and rust-inhibitor formula.',
                '3208',
                '18',
                '1 Litre',
                'Super White',
                '#FFFFFF',
                '380.00',
                '490.00',
                'apcolite_white.jpg'
            ],
            [
                'Enamel Paints',
                'Asian Paints Pro',
                'Apcolite Premium Gloss Enamel',
                'Apcolite Premium High-Gloss Synthetic Enamel',
                'Premium synthetic gloss enamel with mirror-like shine and rust-inhibitor formula.',
                '3208',
                '18',
                '4 Litres',
                'Super White',
                '#FFFFFF',
                '1480.00',
                '1890.00',
                'apcolite_white_4l.jpg'
            ],
            [
                'Enamel Paints',
                'Asian Paints Pro',
                'Apcolite Premium Gloss Enamel',
                'Apcolite Premium High-Gloss Synthetic Enamel',
                'Premium synthetic gloss enamel with mirror-like shine and rust-inhibitor formula.',
                '3208',
                '18',
                '1 Litre',
                'Royal Blue',
                '#1E3A8A',
                '410.00',
                '530.00',
                'apcolite_blue.jpg'
            ],
            [
                'Cotton Fabrics',
                'Vardhman Textiles',
                'Organic Combed Cambric Cotton 60s',
                'Fine Cambric Combed Cotton Fabric Roll',
                '100% fine combed cotton weave for luxury apparel.',
                '5208',
                '5',
                '25 Meters',
                'Optic White',
                '#F8F9FA',
                '2400.00',
                '3000.00',
                'cotton_cambric.jpg'
            ]
        ];

        $tempPath = storage_path('app/temp_bulk_sample_' . uniqid() . '.csv');
        $fp = fopen($tempPath, 'w');
        // UTF-8 BOM
        fputs($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers);
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        return $tempPath;
    }

    /**
     * Parse spreadsheet file (.xlsx, .xls, .csv) and import products with variants & images
     */
    public function importSpreadsheet($uploadedFile, $uploadedImageFiles = [], $uploadedZip = null, $userId = null)
    {
        $userId = $userId ?: (Auth::check() ? Auth::id() : 1);
        $company = Company::where('user_id', $userId)->first();
        if (!$company) {
            throw new \Exception("Seller company profile not found. Please contact administration.");
        }

        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $filePath  = $uploadedFile->getRealPath();

        // 1. Prepare images lookup table
        $companionImages = [];
        
        // Handle companion multi-image uploads
        if (!empty($uploadedImageFiles)) {
            foreach ($uploadedImageFiles as $imgFile) {
                if ($imgFile && $imgFile->isValid()) {
                    $origName = strtolower(trim($imgFile->getClientOriginalName()));
                    $ext = $imgFile->getClientOriginalExtension() ?: 'jpg';
                    $newFileName = 'product/bulk_' . time() . '_' . uniqid() . '.' . $ext;
                    $destinationPath = public_path('storage/product');
                    if (!File::isDirectory($destinationPath)) {
                        File::makeDirectory($destinationPath, 0755, true, true);
                    }
                    $imgFile->move($destinationPath, basename($newFileName));
                    $companionImages[$origName] = $newFileName;
                }
            }
        }

        // Handle companion ZIP archive of images
        if ($uploadedZip && $uploadedZip->isValid()) {
            $zip = new ZipArchive();
            if ($zip->open($uploadedZip->getRealPath()) === true) {
                $destinationPath = public_path('storage/product');
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true, true);
                }
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $stream = $zip->getStream($filename);
                        if ($stream) {
                            $baseOrigName = strtolower(basename($filename));
                            $newFileName = 'product/bulk_' . time() . '_' . uniqid() . '.' . $ext;
                            file_put_contents($destinationPath . '/' . basename($newFileName), stream_get_contents($stream));
                            fclose($stream);
                            $companionImages[$baseOrigName] = $newFileName;
                        }
                    }
                }
                $zip->close();
            }
        }

        // 2. Extract embedded drawings & rows if XLSX
        $embeddedDrawingImagesByRow = [];
        $rawRows = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            $parsed = $this->parseXlsxFile($filePath);
            $rawRows = $parsed['rows'];
            $embeddedDrawingImagesByRow = $parsed['embedded_images'];
        } else {
            $rawRows = $this->parseCsvFile($filePath);
        }

        if (empty($rawRows)) {
            throw new \Exception("The spreadsheet appears to be empty or has no data rows.");
        }

        // Standardize header row
        $headerRow = array_shift($rawRows);
        $cleanHeaders = [];
        foreach ($headerRow as $idx => $headerText) {
            $cleanHeaders[$idx] = $this->normalizeHeader($headerText);
        }

        $importedProductsCount = 0;
        $importedVariantsCount = 0;
        $importedImagesCount   = count($companionImages);
        $skippedRows = [];

        // 3. Group rows by Product Name
        $productGroups = [];
        $rowNum = 1;

        foreach ($rawRows as $row) {
            $rowNum++;
            if (empty(array_filter($row, fn($v) => trim($v) !== ''))) {
                continue; // Skip blank rows
            }

            $mappedRow = [];
            foreach ($cleanHeaders as $colIdx => $colName) {
                $mappedRow[$colName] = isset($row[$colIdx]) ? trim((string)$row[$colIdx]) : '';
            }

            $productName = $mappedRow['product_name'] ?? '';
            if (empty($productName)) {
                $skippedRows[] = "Row #{$rowNum}: Product Name is missing.";
                continue;
            }

            // Check if there was an embedded drawing image for this row
            if (isset($embeddedDrawingImagesByRow[$rowNum]) && empty($mappedRow['image_url_or_filename'])) {
                $mappedRow['image_url_or_filename'] = $embeddedDrawingImagesByRow[$rowNum];
            }

            $groupKey = strtolower(trim($productName));
            if (!isset($productGroups[$groupKey])) {
                $productGroups[$groupKey] = [
                    'product_name' => $productName,
                    'category'     => $mappedRow['category_name'] ?? '',
                    'brand'        => $mappedRow['brand_name'] ?? '',
                    'title'        => $mappedRow['title'] ?? '',
                    'description'  => $mappedRow['description'] ?? '',
                    'hsn_code'     => $mappedRow['hsn_code'] ?? '0000',
                    'tax_percent'  => (int)($mappedRow['tax_percent'] ?? 18),
                    'variants'     => []
                ];
            }

            $productGroups[$groupKey]['variants'][] = [
                'row_number'     => $rowNum,
                'box_packing'    => $mappedRow['box_packing'] ?? 'Standard',
                'shade_or_color' => $mappedRow['shade_or_color'] ?? 'Standard',
                'shade_hexcode'  => $mappedRow['shade_hexcode'] ?? '',
                'seller_price'   => (float)($mappedRow['seller_price'] ?? 0),
                'customer_mrp'   => (float)($mappedRow['customer_mrp'] ?? 0),
                'image_ref'      => $mappedRow['image_url_or_filename'] ?? ''
            ];
        }

        if (empty($productGroups)) {
            throw new \Exception("No valid product records found in the spreadsheet.");
        }

        // 4. Database Transaction Insertion
        DB::beginTransaction();
        try {
            foreach ($productGroups as $group) {
                $productName = $group['product_name'];
                $categoryName = $group['category'];
                $brandName    = $group['brand'];
                $title        = !empty($group['title']) ? $group['title'] : $productName;
                $description  = !empty($group['description']) ? $group['description'] : $title;
                $hsnCode      = !empty($group['hsn_code']) ? $group['hsn_code'] : '0000';
                $tax          = (int)($group['tax_percent'] ?: 18);

                // Resolve Category
                $category = null;
                if (!empty($categoryName)) {
                    $category = Category::where('name', 'like', $categoryName)->first();
                }
                if (!$category) {
                    $category = Category::whereIn('status', ['Active', '1', 1])->first() ?? Category::first();
                }
                $categoryId     = $category ? $category->id : 1;
                $mainCategoryId = $category ? ($category->maincategory_id ?: $category->mid ?: 1) : 1;

                // Resolve Brand
                $brand = null;
                if (!empty($brandName)) {
                    $brand = Brand::where('name', 'like', $brandName)->where('user_id', $userId)->first();
                    if (!$brand) {
                        $brand = Brand::where('name', 'like', $brandName)->first();
                    }
                }
                if (!$brand) {
                    $brand = Brand::create([
                        'name'          => !empty($brandName) ? $brandName : 'Seller Brand',
                        'user_id'       => $userId,
                        'company_id'    => $company->id,
                        'category_id'   => $categoryId,
                        'mid'           => $mainCategoryId,
                        'status'        => 'Active',
                        'adminresponse' => 'Approved'
                    ]);
                }
                $brandId = $brand->id;

                // Resolve Images for this Product
                $productImages = [];
                foreach ($group['variants'] as $variant) {
                    $imageRef = $variant['image_ref'];
                    if (empty($imageRef)) {
                        continue;
                    }

                    // Check if it's already a saved storage path (from embedded drawings)
                    if (str_starts_with($imageRef, 'product/')) {
                        $productImages[] = $imageRef;
                        continue;
                    }

                    // Check if it's a companion image upload
                    $lowerRef = strtolower(basename($imageRef));
                    if (isset($companionImages[$lowerRef])) {
                        $productImages[] = $companionImages[$lowerRef];
                        continue;
                    }

                    // Check if it's an HTTP URL
                    if (filter_var($imageRef, FILTER_VALIDATE_URL)) {
                        $downloadedPath = $this->downloadRemoteImage($imageRef);
                        if ($downloadedPath) {
                            $productImages[] = $downloadedPath;
                            $importedImagesCount++;
                            continue;
                        }
                    }

                    // Check if file already exists in public storage
                    if (File::exists(public_path('storage/product/' . basename($imageRef)))) {
                        $productImages[] = 'product/' . basename($imageRef);
                    }
                }

                $productImages = array_unique(array_filter($productImages));
                $primaryImage = !empty($productImages) ? $productImages[0] : 'default.png';
                if (empty($productImages)) {
                    $productImages = [$primaryImage];
                }

                // Find or Create Parent Product
                $product = Product::where('user_id', $userId)
                    ->where('name', $productName)
                    ->first();

                if (!$product) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $productName))) . '-' . rand(1000, 99999);
                    $product = Product::create([
                        'user_id'         => $userId,
                        'brand_id'        => $brandId,
                        'category_id'     => $categoryId,
                        'maincategory_id' => $mainCategoryId,
                        'subcategory_id'  => 0,
                        'name'            => $productName,
                        'slug'            => $slug,
                        'title'           => $title,
                        'description'     => $description,
                        'image'           => $primaryImage,
                        'multipleimages'  => json_encode($productImages),
                        'hsncode'         => $hsnCode,
                        'tax'             => $tax,
                        'status'          => '1',
                        'product_id'      => rand(100000, 999999),
                    ]);
                    $importedProductsCount++;
                } else {
                    // Update metadata and append new images if provided
                    $existingImages = json_decode($product->multipleimages, true) ?: [$product->image];
                    $mergedImages = array_unique(array_filter(array_merge($existingImages, $productImages)));
                    $product->update([
                        'brand_id'        => $brandId,
                        'category_id'     => $categoryId,
                        'maincategory_id' => $mainCategoryId,
                        'title'           => $title,
                        'description'     => $description,
                        'hsncode'         => $hsnCode,
                        'tax'             => $tax,
                        'multipleimages'  => json_encode(array_values($mergedImages)),
                        'image'           => $product->image !== 'default.png' ? $product->image : $primaryImage,
                        'status'          => '1'
                    ]);
                }

                // 5. Process Variants (Sizes & Shade Cards)
                $commissionRate = $this->pricingService->getEffectiveCommissionRate($userId, $product->id);

                foreach ($group['variants'] as $v) {
                    $boxPackingName = trim($v['box_packing']);
                    $shadeName      = trim($v['shade_or_color']);
                    $shadeHexcode   = trim($v['shade_hexcode']);
                    $sellerPrice    = (float)$v['seller_price'];
                    $customerMrp    = (float)$v['customer_mrp'];

                    // A. Resolve or Create Box Packing / Size
                    $boxPacking = BoxPacking::where('name', 'like', $boxPackingName)->first();
                    if (!$boxPacking) {
                        $boxPacking = BoxPacking::create([
                            'name'            => $boxPackingName,
                            'pcs'             => 1,
                            'maincategory_id' => $mainCategoryId,
                            'status'          => 'Active'
                        ]);
                    }

                    // B. Resolve or Create Shade Card / Color
                    $shadeCard = ShadeCard::where('name', 'like', $shadeName)->first();
                    if (!$shadeCard) {
                        $hex = !empty($shadeHexcode) ? $shadeHexcode : '#4A5568';
                        $shadeCard = ShadeCard::create([
                            'name'            => $shadeName,
                            'hexcode'         => $hex,
                            'maincategory_id' => $mainCategoryId,
                            'category_id'     => $categoryId,
                            'user_id'         => $userId,
                            'status'          => 'Active'
                        ]);
                    }

                    // Calculate wholesale customer price with seller commission
                    $packLitres = $this->pricingService->parsePackLitres($boxPacking->name);
                    $calculatedPrice = round($sellerPrice * (1 + ($commissionRate / 100)), 2);
                    $finalMrp = $customerMrp > 0 ? $customerMrp : round($calculatedPrice * 1.25, 2);

                    // Insert or Update variant in product_attributes
                    ProductAttributes::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color'      => (string)$shadeCard->id,
                            'quantity'   => (string)$boxPacking->id,
                        ],
                        [
                            'oldprice'        => $finalMrp,
                            'seller_price'    => $sellerPrice,
                            'commission_rate' => $commissionRate,
                            'pack_litres'     => $packLitres,
                            'price'           => $calculatedPrice,
                            'status'          => 'Active'
                        ]
                    );

                    $importedVariantsCount++;
                }
            }

            DB::commit();

            return [
                'success'          => true,
                'products_count'   => $importedProductsCount,
                'variants_count'   => $importedVariantsCount,
                'images_count'     => $importedImagesCount,
                'skipped_rows'     => $skippedRows
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk product upload failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Download a remote image URL securely with timeout
     */
    protected function downloadRemoteImage($url)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($data)) {
                $ext = 'jpg';
                if (str_contains($contentType, 'png'))  $ext = 'png';
                if (str_contains($contentType, 'webp')) $ext = 'webp';

                $destinationPath = public_path('storage/product');
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true, true);
                }

                $fileName = 'bulk_' . time() . '_' . uniqid() . '.' . $ext;
                file_put_contents($destinationPath . '/' . $fileName, $data);
                return 'product/' . $fileName;
            }
        } catch (\Exception $e) {
            Log::warning("Could not download remote image: {$url} - " . $e->getMessage());
        }
        return null;
    }

    /**
     * Parse OpenXML (.xlsx) files and extract cell values plus embedded drawing images
     */
    protected function parseXlsxFile($filePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Unable to open XLSX file. Please ensure it is a valid Excel spreadsheet.");
        }

        // 1. Read Shared Strings (if any)
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $sXml = simplexml_load_string($stringsXml);
            if ($sXml && isset($sXml->si)) {
                foreach ($sXml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $partText = '';
                        foreach ($si->r as $r) {
                            $partText .= (string)$r->t;
                        }
                        $sharedStrings[] = $partText;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Read embedded drawing images
        $embeddedImagesByRow = [];
        $drawingRels = [];
        $drawingRelsXml = $zip->getFromName('xl/drawings/_rels/drawing1.xml.rels');
        if ($drawingRelsXml) {
            $drels = simplexml_load_string($drawingRelsXml);
            if ($drels && isset($drels->Relationship)) {
                foreach ($drels->Relationship as $rel) {
                    $id = (string)$rel['Id'];
                    $target = (string)$rel['Target'];
                    // Target usually like ../media/image1.png
                    $mediaPath = 'xl/' . ltrim(str_replace('../', '', $target), '/');
                    $drawingRels[$id] = $mediaPath;
                }
            }
        }

        $drawingXml = $zip->getFromName('xl/drawings/drawing1.xml');
        if ($drawingXml && !empty($drawingRels)) {
            $dXml = simplexml_load_string($drawingXml);
            if ($dXml) {
                // Register namespaces
                $dXml->registerXPathNamespace('xdr', 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing');
                $dXml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                $anchors = $dXml->xpath('//xdr:twoCellAnchor | //xdr:oneCellAnchor');
                if ($anchors) {
                    $destinationPath = public_path('storage/product');
                    if (!File::isDirectory($destinationPath)) {
                        File::makeDirectory($destinationPath, 0755, true, true);
                    }

                    foreach ($anchors as $anchor) {
                        $fromRow = (int)$anchor->from->row; // 0-indexed in XML, so row 1 = index 0
                        $rowNumber = $fromRow + 1;

                        $blip = $anchor->xpath('.//a:blip');
                        if (!empty($blip)) {
                            $rId = (string)$blip[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->embed;
                            if (isset($drawingRels[$rId])) {
                                $mediaZipPath = $drawingRels[$rId];
                                $mediaStream = $zip->getStream($mediaZipPath);
                                if ($mediaStream) {
                                    $ext = pathinfo($mediaZipPath, PATHINFO_EXTENSION) ?: 'jpg';
                                    $savedFileName = 'bulk_' . time() . '_' . uniqid() . '.' . $ext;
                                    file_put_contents($destinationPath . '/' . $savedFileName, stream_get_contents($mediaStream));
                                    fclose($mediaStream);
                                    $embeddedImagesByRow[$rowNumber] = 'product/' . $savedFileName;
                                }
                            }
                        }
                    }
                }
            }
        }

        // 3. Read Worksheet Rows
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \Exception("Worksheet 1 could not be found in the Excel document.");
        }

        $xml = simplexml_load_string($sheetXml);
        $rows = [];

        if ($xml && isset($xml->sheetData->row)) {
            foreach ($xml->sheetData->row as $rowElem) {
                $rNum = (int)$rowElem['r'];
                $rowData = [];
                $highestCol = 0;

                foreach ($rowElem->c as $cell) {
                    $ref = (string)$cell['r'];
                    $colLetters = preg_replace('/[0-9]/', '', $ref);
                    $colIndex = $this->columnLetterToIndex($colLetters);
                    $highestCol = max($highestCol, $colIndex);

                    $val = '';
                    $type = (string)$cell['t'];

                    if ($type === 's' && isset($cell->v)) {
                        $strIdx = (int)$cell->v;
                        $val = $sharedStrings[$strIdx] ?? '';
                    } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                        $val = (string)$cell->is->t;
                    } elseif (isset($cell->v)) {
                        $val = (string)$cell->v;
                    }

                    $rowData[$colIndex] = $val;
                }

                // Fill gaps
                $denseRow = [];
                for ($c = 0; $c <= $highestCol; $c++) {
                    $denseRow[$c] = $rowData[$c] ?? '';
                }
                $rows[$rNum] = $denseRow;
            }
        }

        $zip->close();
        ksort($rows);
        return [
            'rows'            => array_values($rows),
            'embedded_images' => $embeddedImagesByRow
        ];
    }

    /**
     * Parse standard CSV files
     */
    protected function parseCsvFile($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Unable to read CSV file.");
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    /**
     * Convert Excel column letter (A, B, C... AA, AB) to 0-based index
     */
    protected function columnLetterToIndex($col)
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - 64);
        }
        return $index - 1;
    }

    /**
     * Convert 0-based column index to Excel column letter (0 => A, 1 => B, etc.)
     */
    protected function indexToColumnLetter($index)
    {
        $letter = '';
        $index = $index + 1;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int)(($index - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Normalize header name to standard key
     */
    protected function normalizeHeader($h)
    {
        $clean = strtolower(trim(str_replace(["\xEF\xBB\xBF", ' '], ['', '_'], (string)$h)));
        
        $aliases = [
            'category'       => 'category_name',
            'cat'            => 'category_name',
            'category_name'  => 'category_name',

            'brand'          => 'brand_name',
            'brand_name'     => 'brand_name',

            'product'        => 'product_name',
            'name'           => 'product_name',
            'product_name'   => 'product_name',

            'title'          => 'title',
            'product_title'  => 'title',

            'description'    => 'description',
            'details'        => 'description',
            'desc'           => 'description',

            'hsn'            => 'hsn_code',
            'hsncode'        => 'hsn_code',
            'hsn_code'       => 'hsn_code',

            'tax'            => 'tax_percent',
            'tax_rate'       => 'tax_percent',
            'gst'            => 'tax_percent',
            'tax_percent'    => 'tax_percent',

            'packing'        => 'box_packing',
            'size'           => 'box_packing',
            'box_packing'    => 'box_packing',
            'quantity'       => 'box_packing',

            'shade'          => 'shade_or_color',
            'color'          => 'shade_or_color',
            'colour'         => 'shade_or_color',
            'shade_card'     => 'shade_or_color',
            'shade_or_color' => 'shade_or_color',

            'hex'            => 'shade_hexcode',
            'hexcode'        => 'shade_hexcode',
            'color_code'     => 'shade_hexcode',
            'shade_hexcode'  => 'shade_hexcode',

            'price'          => 'seller_price',
            'seller_price'   => 'seller_price',
            'base_price'     => 'seller_price',
            'factory_price'  => 'seller_price',

            'mrp'            => 'customer_mrp',
            'oldprice'       => 'customer_mrp',
            'retail_price'   => 'customer_mrp',
            'customer_mrp'   => 'customer_mrp',

            'image'          => 'image_url_or_filename',
            'image_url'      => 'image_url_or_filename',
            'image_name'     => 'image_url_or_filename',
            'photo'          => 'image_url_or_filename',
            'image_url_or_filename' => 'image_url_or_filename'
        ];

        return $aliases[$clean] ?? $clean;
    }

    /**
     * Build standard OpenXML worksheet XML
     */
    protected function buildSheetXml(array $headers, array $rows)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Headers
        $xml .= '    <row r="1">' . "\n";
        foreach ($headers as $cIdx => $headerVal) {
            $cellRef = $this->indexToColumnLetter($cIdx) . '1';
            $escaped = htmlspecialchars((string)$headerVal, ENT_XML1, 'UTF-8');
            $xml .= "      <c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>\n";
        }
        $xml .= '    </row>' . "\n";

        // Rows 2..N: Data
        $rNum = 2;
        foreach ($rows as $row) {
            $xml .= "    <row r=\"{$rNum}\">\n";
            foreach ($row as $cIdx => $val) {
                $cellRef = $this->indexToColumnLetter($cIdx) . $rNum;
                $escaped = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                $xml .= "      <c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>\n";
            }
            $xml .= "    </row>\n";
            $rNum++;
        }

        $xml .= '  </sheetData>' . "\n";
        $xml .= '</worksheet>';
        return $xml;
    }
}
