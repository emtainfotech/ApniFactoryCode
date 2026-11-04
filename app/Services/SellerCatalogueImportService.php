<?php

namespace App\Services;

use App\Jobs\ProcessSellerCatalogueImport;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\BoxPacking;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\SellerCatalogueImport;
use App\Models\SellerListing;
use App\Models\ShadeCard;
use App\Models\SubCatgory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class SellerCatalogueImportService
{
    /**
     * Generate an Excel or CSV import template with standard column headers and reference sheets.
     */
    public function generateTemplate(Company $company, string $format = 'xlsx'): string
    {
        $format = strtolower($format);
        $tempPath = storage_path('app/temp_import_template_' . uniqid() . '.' . $format);

        $headers = [
            'action',                 // CREATE or UPDATE (optional, inferred by SKU existence)
            'product_id',             // Optional: Parent product ID for updates or grouping
            'product_name',           // Mandatory for new products
            'product_title',          // Marketing title
            'category_name',          // Mandatory: Name or ID of category
            'subcategory_name',       // Mandatory: Name or ID of subcategory
            'brand_name',             // Mandatory: Name or ID of brand
            'hsn_code',               // HSN code
            'tax_percent',            // Tax rate (e.g. 18)
            'product_status',         // Active / Inactive
            'variant_sku',            // Variant SKU code
            'variant_name',           // Variant display name
            'shade_or_color',         // Color / shade name
            'color_hex',              // Hex code (e.g. #FFFFFF)
            'size_or_packing',        // Size or packing name (e.g. 1 Litre)
            'variant_status',         // Active / Inactive
            'dynamic_attributes',     // Key: Value pairs delimited by semicolon
            'seller_sku',             // MANDATORY & UNIQUE: Sellable item SKU
            'barcode',                // Barcode / GTIN
            'unit_of_measurement',    // Litre, Kg, Meter, Piece, Box, etc.
            'case_pack_quantity',     // Pack quantity (e.g. 6)
            'min_order_quantity',     // MOQ (e.g. 1)
            'order_multiple',         // Multiples (e.g. 1)
            'allow_unit_orders',      // Yes / No
            'allow_carton_orders',    // Yes / No
            'seller_price',           // Wholesale factory price (INR)
            'commission_rate',        // Commission % (optional)
            'selling_price',          // Customer selling price (optional, auto-calculated if blank)
            'customer_mrp',           // MRP (INR)
            'stock_quantity',         // Available stock count
            'low_stock_threshold',    // Low stock alert threshold
            'dispatch_lead_days',     // Days to dispatch
            'listing_status',         // Active / Inactive / Out of Stock
        ];

        $sampleRow = [
            'CREATE',
            '',
            'Apcolite Premium Gloss Enamel',
            'Apcolite Premium High-Gloss Synthetic Enamel',
            'Paints & Coatings',
            'Industrial Enamels',
            'Asian Paints Pro',
            '3208',
            '18',
            'Active',
            'APC-WHT-1L',
            'Super White - 1 Litre',
            'Super White',
            '#FFFFFF',
            '1 Litre',
            'Active',
            'Finish Type: High Gloss Mirror; Material: Synthetic Enamel',
            'ALPH-APC-WHT-1L',
            '8901234567890',
            'Litre',
            '6',
            '2',
            '2',
            'Yes',
            'Yes',
            '350.00',
            '10.00',
            '385.00',
            '450.00',
            '100',
            '15',
            '2',
            'Active',
        ];

        if ($format === 'csv') {
            $handle = fopen($tempPath, 'w+');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, $headers);
            fputcsv($handle, $sampleRow);
            fclose($handle);
            return $tempPath;
        }

        // OpenXML XLSX Template with reference sheets
        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Unable to create template XLSX file.");
        }

        // Content Types
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

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>
  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet4.xml"/>
</Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Import_Catalogue" sheetId="1" r:id="rId1"/>
    <sheet name="Allowed_Categories" sheetId="2" r:id="rId2"/>
    <sheet name="Allowed_Brands" sheetId="3" r:id="rId3"/>
    <sheet name="Dynamic_Attributes" sheetId="4" r:id="rId4"/>
  </sheets>
</workbook>');

        // Sheet 1: Template
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildXmlSheet($headers, [$sampleRow]));

        // Sheet 2: Categories Reference
        $categories = Category::whereIn('status', ['Active', '1', 1])->with('subcategories')->orderBy('name')->get();
        $catRows = [];
        foreach ($categories as $cat) {
            if ($cat->subcategories && $cat->subcategories->count() > 0) {
                foreach ($cat->subcategories as $sub) {
                    $catRows[] = [$cat->id, $cat->name, $sub->id, $sub->name];
                }
            } else {
                $catRows[] = [$cat->id, $cat->name, '', ''];
            }
        }
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->buildXmlSheet(['Category ID', 'Category Name', 'Subcategory ID', 'Subcategory Name'], $catRows));

        // Sheet 3: Brands Reference
        $brands = Brand::whereIn('status', ['Active', '1', 1])->orderBy('name')->get();
        $brandRows = [];
        foreach ($brands as $b) {
            $brandRows[] = [$b->id, $b->name];
        }
        $zip->addFromString('xl/worksheets/sheet3.xml', $this->buildXmlSheet(['Brand ID', 'Brand Name'], $brandRows));

        // Sheet 4: Dynamic Attributes Reference
        $attributes = Attribute::whereIn('status', ['Active', '1', 1])->with('options')->orderBy('name')->get();
        $attrRows = [];
        foreach ($attributes as $a) {
            $optionsList = $a->options ? $a->options->pluck('label')->implode(', ') : '';
            $attrRows[] = [$a->name, $a->code, $a->type, $optionsList];
        }
        $zip->addFromString('xl/worksheets/sheet4.xml', $this->buildXmlSheet(['Attribute Name', 'Unique Code', 'Type', 'Allowed Options (If Select)'], $attrRows));

        $zip->close();
        return $tempPath;
    }

    /**
     * Stage 1: Upload and perform deep row-by-row validation without mutating catalogue records.
     */
    public function uploadAndValidate(Company $company, User $user, UploadedFile $file): SellerCatalogueImport
    {
        // Enforce Phase 2.1 permissions
        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('excel.upload') && !$user->hasSellerPermission('excel.validate')) {
                abort(403, 'Permission denied: excel.upload or excel.validate required.');
            }
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'csv', 'txt'], true)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file must be a valid Excel (.xlsx) or CSV (.csv) file.',
            ]);
        }

        $relativeDir = 'imports/company_' . $company->id;
        $fullDir = storage_path('app/' . $relativeDir);
        if (!File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $savedFileName = 'import_' . $timestamp . '_' . Str::random(8) . '.' . $extension;
        $file->move($fullDir, $savedFileName);
        $fullPath = $fullDir . DIRECTORY_SEPARATOR . $savedFileName;

        $import = SellerCatalogueImport::create([
            'company_id'        => $company->id,
            'user_id'           => $user->id,
            'original_filename' => $file->getClientOriginalName(),
            'file_path'         => $relativeDir . '/' . $savedFileName,
            'file_size'         => file_exists($fullPath) ? filesize($fullPath) : 0,
            'import_format'     => $extension === 'xlsx' ? 'xlsx' : 'csv',
            'status'            => 'validating',
            'started_at'        => now(),
            'ip_address'        => request() ? request()->ip() : null,
        ]);

        try {
            // Parse rows from file
            $rawRows = $this->parseUploadedFile($fullPath, $extension);

            if (empty($rawRows) || count($rawRows) < 2) {
                throw new \InvalidArgumentException('The spreadsheet is empty or contains no data rows.');
            }

            // Headers row (Row 1)
            $headerRow = array_shift($rawRows);
            $normalizedHeaders = $this->normalizeHeaders($headerRow);

            // Verify critical header columns
            $requiredHeaders = ['seller_sku', 'product_name'];
            foreach ($requiredHeaders as $req) {
                if (!in_array($req, $normalizedHeaders, true)) {
                    throw new \InvalidArgumentException("Required column [{$req}] is missing from the uploaded spreadsheet header.");
                }
            }

            $canUpdatePricing   = $user->isAdmin() || $user->hasSellerPermission('pricing.update');
            $canUpdateInventory = $user->isAdmin() || $user->hasSellerPermission('inventory.update');

            // Perform deep row-wise validation
            $validationResults = $this->validateRows(
                $company,
                $rawRows,
                $normalizedHeaders,
                $canUpdatePricing,
                $canUpdateInventory
            );

            $errorReportPath = null;
            if (!empty($validationResults['errors'])) {
                $errorReportPath = $this->generateErrorReport(
                    $company,
                    $import->id,
                    $validationResults['errors']
                );
            }

            $importStatus = $validationResults['valid_count'] > 0 ? 'validated' : 'validation_failed';

            $import->update([
                'status'             => $importStatus,
                'total_rows'         => $validationResults['total_count'],
                'valid_rows'         => $validationResults['valid_count'],
                'invalid_rows'       => $validationResults['invalid_count'],
                'error_report_path'  => $errorReportPath,
                'validation_summary' => [
                    'headers'       => $normalizedHeaders,
                    'summary'       => [
                        'total'     => $validationResults['total_count'],
                        'valid'     => $validationResults['valid_count'],
                        'invalid'   => $validationResults['invalid_count'],
                        'creates'   => $validationResults['create_count'],
                        'updates'   => $validationResults['update_count'],
                    ],
                    'rows'          => $validationResults['rows_data'],
                ],
                'error_summary'      => [
                    'errors_count'  => count($validationResults['errors']),
                    'sample_errors' => array_slice($validationResults['errors'], 0, 20),
                ],
            ]);

            return $import->fresh();
        } catch (\Throwable $e) {
            Log::error("Catalogue import validation failed for #{$import->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);

            $import->update([
                'status'        => 'validation_failed',
                'error_summary' => ['fatal_error' => $e->getMessage()],
            ]);

            throw ValidationException::withMessages([
                'file' => 'Import validation failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Stage 2: Confirm and execute import of validated catalogue records.
     */
    public function executeImport(SellerCatalogueImport $import, bool $skipInvalid = true): SellerCatalogueImport
    {
        $user = auth()->user() ?: $import->user;

        // Enforce Phase 2.1 permissions
        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('excel.commit')) {
                abort(403, 'Permission denied: excel.commit required to apply catalogue imports.');
            }
        }

        // Must be in validated status
        if ($import->status !== 'validated') {
            throw ValidationException::withMessages([
                'import' => "Import #{$import->id} cannot be executed because its status is [{$import->status}]. Only 'validated' imports can be executed.",
            ]);
        }

        // Prevent duplicate execution
        $import->update([
            'status'     => 'processing',
            'started_at' => $import->started_at ?: now(),
        ]);

        $company = $import->company;
        $summary = $import->validation_summary;
        $rowsData = $summary['rows'] ?? [];

        $createdProducts = 0;
        $updatedProducts = 0;
        $createdVariants = 0;
        $updatedVariants = 0;
        $createdListings = 0;
        $updatedListings = 0;
        $failedRows      = 0;

        foreach ($rowsData as $rowMeta) {
            // Skip invalid rows if skipInvalid is enabled
            if (!$rowMeta['is_valid']) {
                $failedRows++;
                continue;
            }

            try {
                DB::transaction(function () use (
                    $company,
                    $import,
                    $rowMeta,
                    &$createdProducts,
                    &$updatedProducts,
                    &$createdVariants,
                    &$updatedVariants,
                    &$createdListings,
                    &$updatedListings
                ) {
                    $row = $rowMeta['data'];
                    $operation = $rowMeta['operation']; // CREATE or UPDATE
                    $ownerUserId = $company->user_id ?: $import->user_id;

                    // 1. Resolve or Create Parent Product
                    $product = null;
                    if (!empty($row['product_id'])) {
                        $product = Product::where('id', $row['product_id'])->first();
                    }

                    if (!$product) {
                        // Check if an existing product with same name + category + brand already owned by seller exists
                        $product = Product::where('user_id', $ownerUserId)
                            ->where('name', $row['product_name'])
                            ->where('category_id', $row['category_id'])
                            ->where('brand_id', $row['brand_id'])
                            ->first();
                    }

                    if (!$product) {
                        // Create New Parent Product
                        $product = Product::create([
                            'product_id'     => 'PRD-' . strtoupper(Str::random(8)),
                            'user_id'        => $ownerUserId,
                            'name'           => $row['product_name'],
                            'title'          => $row['product_title'] ?: $row['product_name'],
                            'slug'           => Str::slug($row['product_name'] . '-' . Str::random(5)),
                            'category_id'    => $row['category_id'],
                            'subcategory_id' => $row['subcategory_id'] ?? null,
                            'brand_id'       => $row['brand_id'],
                            'hsncode'        => $row['hsn_code'] ?? null,
                            'tax'            => $row['tax_percent'] ?? 18.00,
                            'status'         => $row['product_status'] ?? 'Active',
                        ]);
                        $createdProducts++;
                    } else {
                        // Update existing parent product if owned by this seller
                        if ($product->user_id == $ownerUserId) {
                            $product->update([
                                'title'          => $row['product_title'] ?: $product->title,
                                'subcategory_id' => $row['subcategory_id'] ?: $product->subcategory_id,
                                'hsncode'        => $row['hsn_code'] ?: $product->hsncode,
                                'tax'            => isset($row['tax_percent']) ? $row['tax_percent'] : $product->tax,
                                'status'         => $row['product_status'] ?: $product->status,
                            ]);
                            $updatedProducts++;
                        }
                    }

                    // 2. Dynamic Attributes Handling
                    if (!empty($row['dynamic_attributes_parsed'])) {
                        foreach ($row['dynamic_attributes_parsed'] as $attrItem) {
                            ProductAttributeValue::updateOrCreate(
                                [
                                    'product_id'   => $product->id,
                                    'attribute_id' => $attrItem['attribute_id'],
                                ],
                                [
                                    'attribute_option_id' => $attrItem['attribute_option_id'] ?? null,
                                    'text_value'          => $attrItem['text_value'] ?? null,
                                    'number_value'        => $attrItem['number_value'] ?? null,
                                ]
                            );
                        }
                    }

                    // 3. Resolve or Create Product Variant
                    $variant = null;
                    if (!empty($row['variant_sku'])) {
                        $variant = ProductVariant::where('product_id', $product->id)
                            ->where('variant_sku', $row['variant_sku'])
                            ->first();
                    }

                    if (!$variant) {
                        // Match by shade card or box packing under this product
                        if (!empty($row['shade_card_id']) || !empty($row['box_packing_id'])) {
                            $variantQuery = ProductVariant::where('product_id', $product->id);
                            if (!empty($row['shade_card_id'])) {
                                $variantQuery->where('shade_card_id', $row['shade_card_id']);
                            }
                            if (!empty($row['box_packing_id'])) {
                                $variantQuery->where('box_packing_id', $row['box_packing_id']);
                            }
                            $variant = $variantQuery->first();
                        }
                    }

                    if (!$variant) {
                        // Create Variant
                        $vSku = !empty($row['variant_sku']) ? $row['variant_sku'] : 'VAR-' . strtoupper(Str::random(8));
                        $variant = ProductVariant::create([
                            'product_id'     => $product->id,
                            'variant_sku'    => $vSku,
                            'variant_name'   => $row['variant_name'] ?: ($product->name . ' - ' . ($row['shade_or_color'] ?? 'Standard')),
                            'shade_card_id'  => $row['shade_card_id'] ?? null,
                            'box_packing_id' => $row['box_packing_id'] ?? null,
                            'barcode'        => $row['barcode'] ?? null,
                            'status'         => $row['variant_status'] ?? 'Active',
                        ]);
                        $createdVariants++;
                    } else {
                        // Update Variant
                        $variant->update([
                            'variant_name' => $row['variant_name'] ?: $variant->variant_name,
                            'barcode'      => $row['barcode'] ?: $variant->barcode,
                            'status'       => $row['variant_status'] ?: $variant->status,
                        ]);
                        $updatedVariants++;
                    }

                    // 4. Resolve or Create Seller Listing
                    $listing = SellerListing::where('company_id', $company->id)
                        ->where('seller_sku', $row['seller_sku'])
                        ->first();

                    $sellerPrice = isset($row['seller_price']) && is_numeric($row['seller_price']) ? (float)$row['seller_price'] : 0.0;
                    $commRate    = isset($row['commission_rate']) && is_numeric($row['commission_rate']) ? (float)$row['commission_rate'] : (float)($company->comission ?: 10.0);
                    $sellingPrice= isset($row['selling_price']) && is_numeric($row['selling_price']) ? (float)$row['selling_price'] : SellerListing::calculateSellingPrice($sellerPrice, $commRate);
                    $mrp         = isset($row['customer_mrp']) && is_numeric($row['customer_mrp']) ? (float)$row['customer_mrp'] : ($sellingPrice * 1.15);

                    $listingData = [
                        'company_id'          => $company->id,
                        'seller_user_id'      => $ownerUserId,
                        'product_id'          => $product->id,
                        'product_variant_id'  => $variant->id,
                        'seller_sku'          => $row['seller_sku'],
                        'barcode'             => $row['barcode'] ?? null,
                        'unit_of_measurement' => $row['unit_of_measurement'] ?? 'Piece',
                        'case_pack_quantity'  => !empty($row['case_pack_quantity']) ? (int)$row['case_pack_quantity'] : 1,
                        'min_order_quantity'  => !empty($row['min_order_quantity']) ? (int)$row['min_order_quantity'] : 1,
                        'order_multiple'      => !empty($row['order_multiple']) ? (int)$row['order_multiple'] : 1,
                        'allow_unit_orders'   => !empty($row['allow_unit_orders']) ? (strtolower($row['allow_unit_orders']) === 'yes' || $row['allow_unit_orders'] === '1') : true,
                        'allow_carton_orders' => !empty($row['allow_carton_orders']) ? (strtolower($row['allow_carton_orders']) === 'yes' || $row['allow_carton_orders'] === '1') : true,
                        'seller_price'        => $sellerPrice,
                        'commission_rate'     => $commRate,
                        'selling_price'       => $sellingPrice,
                        'mrp'                 => $mrp,
                        'stock_quantity'      => isset($row['stock_quantity']) && is_numeric($row['stock_quantity']) ? (int)$row['stock_quantity'] : 0,
                        'low_stock_threshold' => isset($row['low_stock_threshold']) && is_numeric($row['low_stock_threshold']) ? (int)$row['low_stock_threshold'] : 10,
                        'dispatch_lead_days'  => isset($row['dispatch_lead_days']) && is_numeric($row['dispatch_lead_days']) ? (int)$row['dispatch_lead_days'] : 2,
                        'status'              => $row['listing_status'] ?? 'Active',
                    ];

                    if (!$listing) {
                        SellerListing::create($listingData);
                        $createdListings++;
                    } else {
                        $listing->update($listingData);
                        $updatedListings++;
                    }
                });
            } catch (\Throwable $e) {
                Log::warning("Error applying import row [{$rowMeta['row_number']}]: " . $e->getMessage());
                $failedRows++;
            }
        }

        $finalStatus = $failedRows === 0 ? 'completed' : ($createdListings > 0 || $updatedListings > 0 ? 'partial_success' : 'failed');

        $import->update([
            'status'           => $finalStatus,
            'created_products' => $createdProducts,
            'updated_products' => $updatedProducts,
            'created_variants' => $createdVariants,
            'updated_variants' => $updatedVariants,
            'created_listings' => $createdListings,
            'updated_listings' => $updatedListings,
            'failed_rows'      => $failedRows,
            'completed_at'     => now(),
        ]);

        return $import->fresh();
    }

    /**
     * Validate raw rows against categories, brands, dynamic attributes, SKUs, and permissions.
     */
    protected function validateRows(
        Company $company,
        array $rawRows,
        array $headers,
        bool $canUpdatePricing,
        bool $canUpdateInventory
    ): array {
        $seenSkusInFile = [];
        $errors = [];
        $rowsData = [];

        $createCount = 0;
        $updateCount = 0;
        $validCount  = 0;
        $invalidCount= 0;

        // Preload active master data into memory caches for high performance
        $categoriesByName = Category::whereIn('status', ['Active', '1', 1])->get()->keyBy(fn($c) => strtolower(trim($c->name)));
        $categoriesById   = Category::whereIn('status', ['Active', '1', 1])->get()->keyBy('id');

        $subcategoriesByName = SubCatgory::whereIn('status', ['Active', '1', 1])->get()->groupBy(fn($s) => strtolower(trim($s->name)));
        $subcategoriesById   = SubCatgory::whereIn('status', ['Active', '1', 1])->get()->keyBy('id');

        $brandsByName = Brand::whereIn('status', ['Active', '1', 1])->get()->keyBy(fn($b) => strtolower(trim($b->name)));
        $brandsById   = Brand::whereIn('status', ['Active', '1', 1])->get()->keyBy('id');

        $shadesByName = ShadeCard::whereIn('status', ['Active', '1', 1])->get()->keyBy(fn($s) => strtolower(trim($s->name)));
        $packingsByName= BoxPacking::whereIn('status', ['Active', '1', 1])->get()->keyBy(fn($p) => strtolower(trim($p->name)));

        $attributesByCode = Attribute::whereIn('status', ['Active', '1', 1])->with('options')->get()->keyBy(fn($a) => strtolower(trim($a->code)));
        $attributesByName = Attribute::whereIn('status', ['Active', '1', 1])->with('options')->get()->keyBy(fn($a) => strtolower(trim($a->name)));

        // Preload existing company SKUs
        $existingCompanySkus = SellerListing::where('company_id', $company->id)
            ->pluck('id', 'seller_sku')
            ->toArray();

        foreach ($rawRows as $index => $rowValues) {
            $rowNum = $index + 2; // Row 1 is header, so data starts at row 2
            $rowErrors = [];

            // Combine headers with row values
            $row = [];
            foreach ($headers as $colIdx => $colKey) {
                $row[$colKey] = isset($rowValues[$colIdx]) ? trim((string)$rowValues[$colIdx]) : '';
            }

            // 1. Validate Seller SKU (MANDATORY & UNIQUE)
            $sku = $row['seller_sku'] ?? '';
            if ($sku === '') {
                $rowErrors[] = [
                    'row'       => $rowNum,
                    'field'     => 'seller_sku',
                    'value'     => '',
                    'error'     => 'Seller SKU is mandatory for every row.',
                    'suggest'   => 'Provide a unique SKU code identifying this listing.',
                ];
            } else {
                // Check duplicate SKU in current file
                if (isset($seenSkusInFile[strtolower($sku)])) {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'seller_sku',
                        'value'     => $sku,
                        'error'     => "Duplicate Seller SKU [{$sku}] encountered within the uploaded file. First seen on row {$seenSkusInFile[strtolower($sku)]}.",
                        'suggest'   => 'Ensure every row in the file has a distinct seller SKU.',
                    ];
                } else {
                    $seenSkusInFile[strtolower($sku)] = $rowNum;
                }
            }

            // Determine Operation
            $operation = 'CREATE';
            if (!empty($sku) && isset($existingCompanySkus[$sku])) {
                $operation = 'UPDATE';
                $updateCount++;
            } else {
                $createCount++;
            }

            // 2. Validate Product Name
            if ($operation === 'CREATE' && empty($row['product_name'])) {
                $rowErrors[] = [
                    'row'       => $rowNum,
                    'field'     => 'product_name',
                    'value'     => '',
                    'error'     => 'Product name is required for creating a new product listing.',
                    'suggest'   => 'Provide the primary product name.',
                ];
            }

            // 3. Validate Category
            $resolvedCatId = null;
            $catInput = $row['category_name'] ?? ($row['category_id'] ?? '');
            if (!empty($catInput)) {
                if (is_numeric($catInput) && isset($categoriesById[(int)$catInput])) {
                    $resolvedCatId = (int)$catInput;
                } elseif (isset($categoriesByName[strtolower($catInput)])) {
                    $resolvedCatId = $categoriesByName[strtolower($catInput)]->id;
                } else {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'category_name',
                        'value'     => $catInput,
                        'error'     => "Category [{$catInput}] is invalid or inactive.",
                        'suggest'   => 'Check the Allowed_Categories reference sheet for valid categories.',
                    ];
                }
            } elseif ($operation === 'CREATE') {
                $rowErrors[] = [
                    'row'       => $rowNum,
                    'field'     => 'category_name',
                    'value'     => '',
                    'error'     => 'Category is mandatory for new catalogue products.',
                    'suggest'   => 'Specify a valid category name or ID.',
                ];
            }
            $row['category_id'] = $resolvedCatId;

            // 4. Validate Subcategory
            $resolvedSubCatId = null;
            $subInput = $row['subcategory_name'] ?? ($row['subcategory_id'] ?? '');
            if (!empty($subInput) && $resolvedCatId) {
                $matchedSub = null;
                if (is_numeric($subInput) && isset($subcategoriesById[(int)$subInput])) {
                    $matchedSub = $subcategoriesById[(int)$subInput];
                } elseif (isset($subcategoriesByName[strtolower($subInput)])) {
                    $matchedSub = $subcategoriesByName[strtolower($subInput)]->first(fn($s) => ($s->category_id == $resolvedCatId || $s->cid == $resolvedCatId));
                }

                if ($matchedSub && ($matchedSub->category_id == $resolvedCatId || $matchedSub->cid == $resolvedCatId)) {
                    $resolvedSubCatId = $matchedSub->id;
                } else {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'subcategory_name',
                        'value'     => $subInput,
                        'error'     => "Subcategory [{$subInput}] does not belong to the selected category.",
                        'suggest'   => 'Select a subcategory that is mapped to the chosen category.',
                    ];
                }
            }
            $row['subcategory_id'] = $resolvedSubCatId;

            // 5. Validate Brand
            $resolvedBrandId = null;
            $brandInput = $row['brand_name'] ?? ($row['brand_id'] ?? '');
            if (!empty($brandInput)) {
                if (is_numeric($brandInput) && isset($brandsById[(int)$brandInput])) {
                    $resolvedBrandId = (int)$brandInput;
                } elseif (isset($brandsByName[strtolower($brandInput)])) {
                    $resolvedBrandId = $brandsByName[strtolower($brandInput)]->id;
                } else {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'brand_name',
                        'value'     => $brandInput,
                        'error'     => "Brand [{$brandInput}] does not exist in master data.",
                        'suggest'   => 'Check the Allowed_Brands reference sheet.',
                    ];
                }
            } elseif ($operation === 'CREATE') {
                $rowErrors[] = [
                    'row'       => $rowNum,
                    'field'     => 'brand_name',
                    'value'     => '',
                    'error'     => 'Brand is mandatory for new catalogue products.',
                    'suggest'   => 'Provide an active brand name from the Allowed_Brands sheet.',
                ];
            }
            $row['brand_id'] = $resolvedBrandId;

            // 6. Validate Pricing & Permissions
            $hasPricingFields = isset($row['seller_price']) && $row['seller_price'] !== '';
            if ($hasPricingFields) {
                if (!$canUpdatePricing) {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'seller_price',
                        'value'     => $row['seller_price'],
                        'error'     => 'Permission denied: pricing.update is required to specify or update pricing.',
                        'suggest'   => 'Remove price columns or request pricing manager role.',
                    ];
                } else {
                    if (!is_numeric($row['seller_price']) || (float)$row['seller_price'] < 0) {
                        $rowErrors[] = [
                            'row'       => $rowNum,
                            'field'     => 'seller_price',
                            'value'     => $row['seller_price'],
                            'error'     => 'Seller price must be a non-negative number.',
                            'suggest'   => 'Provide a valid positive price in INR.',
                        ];
                    }
                    if (isset($row['customer_mrp']) && is_numeric($row['customer_mrp'])) {
                        if ((float)$row['customer_mrp'] < (float)$row['seller_price']) {
                            $rowErrors[] = [
                                'row'       => $rowNum,
                                'field'     => 'customer_mrp',
                                'value'     => $row['customer_mrp'],
                                'error'     => 'Customer MRP cannot be lower than seller wholesale price.',
                                'suggest'   => 'Set MRP equal to or higher than the seller price.',
                            ];
                        }
                    }
                }
            }

            // 7. Validate Inventory & Permissions
            $hasInventoryFields = isset($row['stock_quantity']) && $row['stock_quantity'] !== '';
            if ($hasInventoryFields) {
                if (!$canUpdateInventory) {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'stock_quantity',
                        'value'     => $row['stock_quantity'],
                        'error'     => 'Permission denied: inventory.update is required to modify stock quantities.',
                        'suggest'   => 'Remove stock quantity column or request inventory manager permissions.',
                    ];
                } else {
                    if (!is_numeric($row['stock_quantity']) || (int)$row['stock_quantity'] < 0) {
                        $rowErrors[] = [
                            'row'       => $rowNum,
                            'field'     => 'stock_quantity',
                            'value'     => $row['stock_quantity'],
                            'error'     => 'Stock quantity must be a non-negative integer.',
                            'suggest'   => 'Enter an integer value of 0 or greater.',
                        ];
                    }
                }
            }

            // 8. Validate Pack and Order Multiples
            if (isset($row['case_pack_quantity']) && $row['case_pack_quantity'] !== '') {
                if (!is_numeric($row['case_pack_quantity']) || (int)$row['case_pack_quantity'] < 1) {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'case_pack_quantity',
                        'value'     => $row['case_pack_quantity'],
                        'error'     => 'Case pack quantity must be at least 1.',
                        'suggest'   => 'Enter an integer greater than or equal to 1.',
                    ];
                }
            }
            if (isset($row['min_order_quantity']) && $row['min_order_quantity'] !== '') {
                if (!is_numeric($row['min_order_quantity']) || (int)$row['min_order_quantity'] < 1) {
                    $rowErrors[] = [
                        'row'       => $rowNum,
                        'field'     => 'min_order_quantity',
                        'value'     => $row['min_order_quantity'],
                        'error'     => 'Minimum Order Quantity (MOQ) must be at least 1.',
                        'suggest'   => 'Enter an integer >= 1.',
                    ];
                }
            }

            // 9. Match Shade & Packing if present
            if (!empty($row['shade_or_color'])) {
                $shadeKey = strtolower(trim($row['shade_or_color']));
                if (isset($shadesByName[$shadeKey])) {
                    $row['shade_card_id'] = $shadesByName[$shadeKey]->id;
                }
            }
            if (!empty($row['size_or_packing'])) {
                $packKey = strtolower(trim($row['size_or_packing']));
                if (isset($packingsByName[$packKey])) {
                    $row['box_packing_id'] = $packingsByName[$packKey]->id;
                }
            }

            // 10. Validate Dynamic Attributes
            $parsedAttrs = [];
            if (!empty($row['dynamic_attributes'])) {
                $pairs = explode(';', $row['dynamic_attributes']);
                foreach ($pairs as $pair) {
                    $pair = trim($pair);
                    if ($pair === '' || !str_contains($pair, ':')) continue;

                    [$aName, $aVal] = explode(':', $pair, 2);
                    $aName = trim($aName);
                    $aVal  = trim($aVal);

                    $attr = $attributesByName[strtolower($aName)] ?? ($attributesByCode[strtolower($aName)] ?? null);
                    if (!$attr) {
                        $rowErrors[] = [
                            'row'     => $rowNum,
                            'field'   => 'dynamic_attributes',
                            'value'   => $aName,
                            'error'   => "Dynamic attribute [{$aName}] is not registered in the system.",
                            'suggest' => 'Check the Dynamic_Attributes reference sheet.',
                        ];
                    } else {
                        $optId = null;
                        if (in_array($attr->type, ['select', 'single_select', 'multi_select', 'multi-select']) && $attr->options) {
                            $opt = $attr->options->first(fn($o) => strtolower($o->label) === strtolower($aVal) || strtolower($o->value) === strtolower($aVal));
                            if (!$opt) {
                                $rowErrors[] = [
                                    'row'     => $rowNum,
                                    'field'   => 'dynamic_attributes',
                                    'value'   => "{$aName}: {$aVal}",
                                    'error'   => "Value [{$aVal}] is not an allowed option for attribute [{$aName}].",
                                    'suggest' => 'Permitted options: ' . $attr->options->pluck('label')->implode(', '),
                                ];
                            } else {
                                $optId = $opt->id;
                            }
                        }

                        $parsedAttrs[] = [
                            'attribute_id'        => $attr->id,
                            'attribute_option_id' => $optId,
                            'text_value'          => $optId ? null : $aVal,
                            'number_value'        => is_numeric($aVal) ? (float)$aVal : null,
                        ];
                    }
                }
            }
            $row['dynamic_attributes_parsed'] = $parsedAttrs;

            $isValid = empty($rowErrors);
            if ($isValid) {
                $validCount++;
            } else {
                $invalidCount++;
                foreach ($rowErrors as $err) {
                    $errors[] = $err;
                }
            }

            $rowsData[] = [
                'row_number' => $rowNum,
                'seller_sku' => $sku,
                'operation'  => $operation,
                'is_valid'   => $isValid,
                'errors'     => $rowErrors,
                'data'       => $row,
            ];
        }

        return [
            'total_count'   => count($rawRows),
            'valid_count'   => $validCount,
            'invalid_count' => $invalidCount,
            'create_count'  => $createCount,
            'update_count'  => $updateCount,
            'errors'        => $errors,
            'rows_data'     => $rowsData,
        ];
    }

    /**
     * Parse uploaded file (.xlsx or .csv) into array of rows.
     */
    protected function parseUploadedFile(string $filePath, string $extension): array
    {
        if ($extension === 'csv' || $extension === 'txt') {
            return $this->parseCsv($filePath);
        }

        return $this->parseXlsx($filePath);
    }

    /**
     * Parse standard CSV file with UTF-8 BOM awareness.
     */
    protected function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Unable to open CSV file.");
        }

        // Check & discard BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            // Skip entirely empty lines
            if (count(array_filter($data, fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Parse OpenXML (.xlsx) file using ZipArchive and XML parsing.
     */
    protected function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Unable to open XLSX archive. Ensure it is a valid Excel spreadsheet.");
        }

        // Read sharedStrings.xml
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $sXml = simplexml_load_string($stringsXml);
            if ($sXml && isset($sXml->si)) {
                foreach ($sXml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $pText = '';
                        foreach ($si->r as $r) {
                            $pText .= (string)$r->t;
                        }
                        $sharedStrings[] = $pText;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // Read Sheet 1 XML
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \RuntimeException("Primary worksheet [sheet1.xml] not found in the spreadsheet.");
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

                $denseRow = [];
                for ($c = 0; $c <= $highestCol; $c++) {
                    $denseRow[$c] = $rowData[$c] ?? '';
                }

                // Skip completely blank rows
                if (count(array_filter($denseRow, fn($v) => trim((string)$v) !== '')) > 0) {
                    $rows[] = $denseRow;
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Normalize header strings (lowercase, space/hyphen to underscore, trimmed).
     */
    protected function normalizeHeaders(array $rawHeaders): array
    {
        return array_map(function ($h) {
            $cleaned = strtolower(trim((string)$h));
            $cleaned = str_replace([' ', '-', '/'], '_', $cleaned);
            $cleaned = preg_replace('/[^a-z0-9_]/', '', $cleaned);
            return $cleaned;
        }, $rawHeaders);
    }

    /**
     * Generate downloadable CSV error report with formula injection defense.
     */
    protected function generateErrorReport(Company $company, int $importId, array $errors): string
    {
        $relativeDir = 'imports/company_' . $company->id;
        $fullDir = storage_path('app/' . $relativeDir);
        $fileName = "error_report_import_{$importId}_" . now()->format('Ymd_His') . '.csv';
        $fullPath = $fullDir . DIRECTORY_SEPARATOR . $fileName;

        $handle = fopen($fullPath, 'w+');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($handle, [
            'Excel Row #',
            'Field Name',
            'Submitted Value',
            'Error Description',
            'Suggested Action',
        ]);

        foreach ($errors as $err) {
            fputcsv($handle, [
                $err['row'],
                $err['field'],
                SellerCatalogueExportService::sanitizeCellValue($err['value'] ?? ''),
                $err['error'],
                $err['suggest'] ?? '',
            ]);
        }

        fclose($handle);
        return $relativeDir . '/' . $fileName;
    }

    /**
     * Securely stream error report file to authorized user.
     */
    public function getErrorReportResponse(SellerCatalogueImport $import, User $user): BinaryFileResponse
    {
        if (!$user->isAdmin()) {
            $userCompany = $user->resolveSellerCompany();
            if (!$userCompany || $userCompany->id !== $import->company_id) {
                abort(404, 'Import record not found or unauthorized.');
            }
        }

        if (!$import->error_report_path) {
            abort(404, 'No error report exists for this import.');
        }

        $fullPath = storage_path('app/' . $import->error_report_path);
        if (!file_exists($fullPath)) {
            abort(404, 'The requested error report was not found on the server.');
        }

        return response()->download($fullPath, "import_{$import->id}_errors.csv", [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Helper to build OpenXML worksheet XML.
     */
    protected function buildXmlSheet(array $headers, array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Row 1: Headers
        $xml .= '    <row r="1">' . "\n";
        foreach ($headers as $cIdx => $hVal) {
            $cellRef = $this->indexToColumnLetter($cIdx) . '1';
            $escaped = htmlspecialchars((string)$hVal, ENT_XML1, 'UTF-8');
            $xml .= "      <c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>\n";
        }
        $xml .= '    </row>' . "\n";

        // Rows 2..N: Data
        $rNum = 2;
        foreach ($rows as $row) {
            $xml .= "    <row r=\"{$rNum}\">\n";
            foreach ($row as $cIdx => $val) {
                $cellRef = $this->indexToColumnLetter($cIdx) . $rNum;
                $sanitized = SellerCatalogueExportService::sanitizeCellValue($val);
                $escaped = htmlspecialchars($sanitized, ENT_XML1, 'UTF-8');
                $xml .= "      <c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>\n";
            }
            $xml .= "    </row>\n";
            $rNum++;
        }

        $xml .= '  </sheetData>' . "\n";
        $xml .= '</worksheet>';
        return $xml;
    }

    protected function indexToColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(($index % 26) + 65) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }

    protected function columnLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - 64);
        }
        return $index - 1;
    }
}
