<?php

namespace App\Services;

use App\Jobs\ProcessSellerCatalogueExport;
use App\Models\Company;
use App\Models\Product;
use App\Models\SellerCatalogueExport;
use App\Models\SellerListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class SellerCatalogueExportService
{
    /**
     * Initiate a new export record and optionally dispatch a job or process synchronously.
     */
    public function initiateExport(
        Company $company,
        User $user,
        array $filters = [],
        string $format = 'xlsx',
        ?string $ipAddress = null,
        bool $async = false
    ): SellerCatalogueExport {
        // Enforce Phase 2.1 authorization
        if (!$user->isAdmin() && !$user->hasSellerPermission('excel.export')) {
            abort(403, 'Permission denied: excel.export required to export catalogue data.');
        }

        // Validate format
        $format = strtolower($format);
        if (!in_array($format, ['xlsx', 'csv'], true)) {
            $format = 'xlsx';
        }

        // Validate product_ids ownership if specified
        if (!empty($filters['product_ids']) && is_array($filters['product_ids'])) {
            $this->validateProductOwnership($company, $filters['product_ids']);
        }

        // Create export history record
        $export = SellerCatalogueExport::create([
            'company_id'    => $company->id,
            'user_id'       => $user->id,
            'export_format' => $format,
            'status'        => 'pending',
            'filters'       => $filters,
            'ip_address'    => $ipAddress ?: (request() ? request()->ip() : null),
        ]);

        if ($async) {
            $export->update(['status' => 'processing', 'started_at' => now()]);
            ProcessSellerCatalogueExport::dispatch($export);
        } else {
            $this->processExport($export);
        }

        return $export->fresh();
    }

    /**
     * Process the catalogue export: builds the file and finalizes the export record.
     */
    public function processExport(SellerCatalogueExport $export): SellerCatalogueExport
    {
        $export->update([
            'status'     => 'processing',
            'started_at' => $export->started_at ?: now(),
        ]);

        try {
            $company = $export->company;
            $user    = $export->user;
            $filters = $export->filters ?: [];

            $canViewPricing   = $user->isAdmin() || $user->hasSellerPermission('pricing.view');
            $canViewInventory = $user->isAdmin() || $user->hasSellerPermission('inventory.view');

            $query = $this->buildExportQuery($company, $filters);
            $totalCount = (clone $query)->count();

            $columns = $this->getColumns($canViewPricing, $canViewInventory);

            // Create target directory: storage/app/exports/company_{id}/
            $relativeDir = 'exports/company_' . $company->id;
            $fullDir = storage_path('app/' . $relativeDir);
            if (!File::exists($fullDir)) {
                File::makeDirectory($fullDir, 0755, true);
            }

            $dateSuffix = now()->format('Ymd_His');
            $slugName = Str::slug($company->name ?: 'seller');
            $baseFileName = "catalogue_{$slugName}_{$dateSuffix}.{$export->export_format}";
            $fullFilePath = $fullDir . DIRECTORY_SEPARATOR . $baseFileName;

            if ($export->export_format === 'csv') {
                $this->generateCsv($fullFilePath, $query, $columns, $canViewPricing, $canViewInventory);
            } else {
                $this->generateXlsx($fullFilePath, $company->name, $query, $columns, $canViewPricing, $canViewInventory);
            }

            $fileSize = file_exists($fullFilePath) ? filesize($fullFilePath) : 0;

            $export->update([
                'status'        => 'completed',
                'file_name'     => $baseFileName,
                'file_path'     => $relativeDir . '/' . $baseFileName,
                'file_size'     => $fileSize,
                'total_records' => $totalCount,
                'completed_at'  => now(),
                'expires_at'    => now()->addDays(7), // 7-day retention
            ]);

            return $export->fresh();
        } catch (\Throwable $e) {
            Log::error("Catalogue export #{$export->id} failed: " . $e->getMessage(), [
                'exception' => $e,
            ]);

            $export->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Build the filtered query for the company's seller listings.
     */
    public function buildExportQuery(Company $company, array $filters = []): Builder
    {
        $query = SellerListing::query()
            ->where('company_id', $company->id)
            ->with([
                'product.category',
                'product.subcategory',
                'product.brand',
                'product.attributeValues.attribute',
                'product.attributeValues.option',
                'variant.shadeCard',
                'variant.boxPacking',
                'variant.attributeValues.attribute',
                'variant.attributeValues.option',
            ])
            ->orderBy('product_id', 'asc')
            ->orderBy('id', 'asc');

        // Filter: specific product_ids
        if (!empty($filters['product_ids']) && is_array($filters['product_ids'])) {
            $query->whereIn('product_id', $filters['product_ids']);
        }

        // Filter: single product_id
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Filter: specific listing_ids
        if (!empty($filters['listing_ids']) && is_array($filters['listing_ids'])) {
            $query->whereIn('id', $filters['listing_ids']);
        }

        // Filter: category_id
        if (!empty($filters['category_id'])) {
            $catId = $filters['category_id'];
            $query->whereHas('product', fn($q) => $q->where('category_id', $catId));
        }

        // Filter: subcategory_id
        if (!empty($filters['subcategory_id'])) {
            $subCatId = $filters['subcategory_id'];
            $query->whereHas('product', fn($q) => $q->where('subcategory_id', $subCatId));
        }

        // Filter: brand_id
        if (!empty($filters['brand_id'])) {
            $brandId = $filters['brand_id'];
            $query->whereHas('product', fn($q) => $q->where('brand_id', $brandId));
        }

        // Filter: product_name search
        if (!empty($filters['product_name'])) {
            $term = trim($filters['product_name']);
            $query->whereHas('product', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('title', 'like', "%{$term}%");
            });
        }

        // Filter: seller_sku
        if (!empty($filters['seller_sku'])) {
            $sku = trim($filters['seller_sku']);
            $query->where('seller_sku', 'like', "%{$sku}%");
        }

        // Filter: listing status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter: variant status
        if (!empty($filters['variant_status'])) {
            $vStatus = $filters['variant_status'];
            $query->whereHas('variant', fn($q) => $q->where('status', $vStatus));
        }

        // Filter: stock_status
        if (!empty($filters['stock_status'])) {
            $stk = strtolower($filters['stock_status']);
            if ($stk === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($stk === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($stk === 'low_stock') {
                $query->whereRaw('stock_quantity <= low_stock_threshold AND stock_quantity > 0');
            }
        }

        return $query;
    }

    /**
     * Generate standard OpenXML XLSX document using low-memory chunked streaming.
     */
    protected function generateXlsx(
        string $filePath,
        string $companyName,
        Builder $query,
        array $columns,
        bool $canViewPricing,
        bool $canViewInventory
    ): void {
        $tempXmlFile = tempnam(sys_get_temp_dir(), 'xlsx_sheet_');
        $xmlHandle = fopen($tempXmlFile, 'w+');

        // Write worksheet header
        fwrite($xmlHandle, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        fwrite($xmlHandle, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n");
        fwrite($xmlHandle, '  <sheetData>' . "\n");

        // Row 1: Table Headers
        fwrite($xmlHandle, '    <row r="1">' . "\n");
        $colIdx = 0;
        foreach ($columns as $colKey => $colLabel) {
            $cellRef = $this->indexToColumnLetter($colIdx) . '1';
            $escaped = htmlspecialchars((string)$colLabel, ENT_XML1, 'UTF-8');
            fwrite($xmlHandle, "      <c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>\n");
            $colIdx++;
        }
        fwrite($xmlHandle, '    </row>' . "\n");

        // Rows 2..N: Data (Chunked to keep memory constant)
        $rowNum = 2;
        $hasData = false;

        $query->chunk(150, function ($listings) use ($xmlHandle, &$rowNum, &$hasData, $columns, $canViewPricing, $canViewInventory) {
            foreach ($listings as $listing) {
                $hasData = true;
                $rowData = $this->mapListingToRow($listing, $canViewPricing, $canViewInventory);

                fwrite($xmlHandle, "    <row r=\"{$rowNum}\">\n");
                $cIdx = 0;
                foreach (array_keys($columns) as $key) {
                    $cellRef = $this->indexToColumnLetter($cIdx) . $rowNum;
                    $rawVal = $rowData[$key] ?? '';
                    $sanitized = self::sanitizeCellValue($rawVal);
                    $escaped = htmlspecialchars($sanitized, ENT_XML1, 'UTF-8');

                    // If numeric, write as number; otherwise write as inlineStr
                    if (is_numeric($rawVal) && !str_starts_with((string)$rawVal, '0') && strlen((string)$rawVal) <= 12 && !str_contains((string)$rawVal, '-')) {
                        fwrite($xmlHandle, "      <c r=\"{$cellRef}\"><v>{$escaped}</v></c>\n");
                    } else {
                        fwrite($xmlHandle, "      <c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>\n");
                    }
                    $cIdx++;
                }
                fwrite($xmlHandle, "    </row>\n");
                $rowNum++;
            }
        });

        // If no records found, add an empty placeholder row
        if (!$hasData) {
            fwrite($xmlHandle, "    <row r=\"2\">\n");
            fwrite($xmlHandle, "      <c r=\"A2\" t=\"inlineStr\"><is><t>No catalogue records match the requested filters.</t></is></c>\n");
            fwrite($xmlHandle, "    </row>\n");
        }

        fwrite($xmlHandle, '  </sheetData>' . "\n");
        fwrite($xmlHandle, '</worksheet>');
        fclose($xmlHandle);

        // Package into ZIP as OpenXML .xlsx
        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tempXmlFile);
            throw new \RuntimeException("Unable to open target XLSX package at [{$filePath}].");
        }

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
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
</Relationships>');

        // xl/workbook.xml
        $cleanComp = htmlspecialchars($companyName ?: 'Catalogue', ENT_XML1, 'UTF-8');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . substr($cleanComp, 0, 31) . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

        // Stream worksheet XML into archive
        $zip->addFile($tempXmlFile, 'xl/worksheets/sheet1.xml');
        $zip->close();

        @unlink($tempXmlFile);
    }

    /**
     * Generate UTF-8 encoded CSV with BOM for universal Excel compatibility.
     */
    protected function generateCsv(
        string $filePath,
        Builder $query,
        array $columns,
        bool $canViewPricing,
        bool $canViewInventory
    ): void {
        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new \RuntimeException("Unable to open target CSV file for writing at [{$filePath}].");
        }

        // Write UTF-8 BOM so Excel opens with proper character encoding
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        fputcsv($handle, array_values($columns));

        $hasData = false;
        $query->chunk(150, function ($listings) use ($handle, &$hasData, $columns, $canViewPricing, $canViewInventory) {
            foreach ($listings as $listing) {
                $hasData = true;
                $row = $this->mapListingToRow($listing, $canViewPricing, $canViewInventory);
                $sanitizedRow = [];
                foreach (array_keys($columns) as $colKey) {
                    $sanitizedRow[] = self::sanitizeCellValue($row[$colKey] ?? '');
                }
                fputcsv($handle, $sanitizedRow);
            }
        });

        if (!$hasData) {
            fputcsv($handle, ['No catalogue records match the requested filters.']);
        }

        fclose($handle);
    }

    /**
     * Map a SellerListing model instance to an associative row.
     */
    public function mapListingToRow(SellerListing $listing, bool $canViewPricing, bool $canViewInventory): array
    {
        $product = $listing->product;
        $variant = $listing->variant;

        // Compile combined dynamic attributes
        $attrPairs = [];
        if ($product && $product->attributeValues) {
            foreach ($product->attributeValues as $val) {
                if ($val->attribute && $val->resolved_value !== null && $val->resolved_value !== '') {
                    $attrPairs[] = "{$val->attribute->name}: {$val->resolved_value}";
                }
            }
        }
        if ($variant && $variant->attributeValues) {
            foreach ($variant->attributeValues as $val) {
                if ($val->attribute && $val->resolved_value !== null && $val->resolved_value !== '') {
                    $attrPairs[] = "{$val->attribute->name}: {$val->resolved_value}";
                }
            }
        }
        $dynamicAttributesSummary = implode('; ', array_unique($attrPairs));

        $row = [
            'product_id'            => $product ? $product->id : ($listing->product_id ?? ''),
            'product_name'          => $product ? $product->name : '',
            'product_title'         => $product ? ($product->title ?: $product->name) : '',
            'category_name'         => $product && $product->category ? $product->category->name : '',
            'subcategory_name'      => $product && $product->subcategory ? $product->subcategory->name : '',
            'brand_name'            => $product && $product->brand ? $product->brand->name : '',
            'hsn_code'              => $product ? ($product->hsncode ?? '') : '',
            'tax_percent'           => $product ? ($product->tax ?? '') : '',
            'product_status'        => $product ? ($product->status ?? '') : '',
            'variant_id'            => $variant ? $variant->id : ($listing->product_variant_id ?? ''),
            'variant_sku'           => $variant ? ($variant->variant_sku ?? '') : '',
            'variant_name'          => $variant ? ($variant->variant_name ?? '') : '',
            'shade_or_color'        => $variant && $variant->shadeCard ? $variant->shadeCard->name : '',
            'color_hex'             => $variant && $variant->shadeCard ? $variant->shadeCard->hexcode : '',
            'size_or_packing'       => $variant && $variant->boxPacking ? $variant->boxPacking->name : '',
            'variant_status'        => $variant ? ($variant->status ?? '') : '',
            'dynamic_attributes'    => $dynamicAttributesSummary,
            'seller_sku'            => $listing->seller_sku ?? '',
            'barcode'               => $listing->barcode ?? '',
            'unit_of_measurement'   => $listing->unit_of_measurement ?? '',
            'case_pack_quantity'    => $listing->case_pack_quantity ?? 1,
            'min_order_quantity'    => $listing->min_order_quantity ?? 1,
            'order_multiple'        => $listing->order_multiple ?? 1,
            'allow_unit_orders'     => $listing->allow_unit_orders ? 'Yes' : 'No',
            'allow_carton_orders'   => $listing->allow_carton_orders ? 'Yes' : 'No',
            'listing_status'        => $listing->status ?? '',
        ];

        // Seller sensitive: Pricing
        if ($canViewPricing) {
            $row['seller_price']    = $listing->seller_price !== null ? (string)$listing->seller_price : '';
            $row['commission_rate'] = $listing->commission_rate !== null ? (string)$listing->commission_rate : '';
            $row['selling_price']   = $listing->selling_price !== null ? (string)$listing->selling_price : '';
            $row['customer_mrp']    = $listing->mrp !== null ? (string)$listing->mrp : '';
        }

        // Seller sensitive: Inventory
        if ($canViewInventory) {
            $row['stock_quantity']      = $listing->stock_quantity !== null ? (string)$listing->stock_quantity : '0';
            $row['reserved_stock']      = $listing->reserved_stock !== null ? (string)$listing->reserved_stock : '0';
            $row['available_stock']     = (string) $listing->available_stock;
            $row['low_stock_threshold'] = $listing->low_stock_threshold !== null ? (string)$listing->low_stock_threshold : '';
            $row['dispatch_lead_days']  = $listing->dispatch_lead_days !== null ? (string)$listing->dispatch_lead_days : '';
        }

        $row['created_at'] = $listing->created_at ? $listing->created_at->format('Y-m-d H:i:s') : '';
        $row['updated_at'] = $listing->updated_at ? $listing->updated_at->format('Y-m-d H:i:s') : '';

        return $row;
    }

    /**
     * Get the ordered columns list, adhering to role permissions.
     */
    public function getColumns(bool $canViewPricing, bool $canViewInventory): array
    {
        $cols = [
            'product_id'          => 'Product ID',
            'product_name'        => 'Product Name',
            'product_title'       => 'Product Title',
            'category_name'       => 'Category',
            'subcategory_name'    => 'Subcategory',
            'brand_name'          => 'Brand',
            'hsn_code'            => 'HSN Code',
            'tax_percent'         => 'Tax (%)',
            'product_status'      => 'Product Status',
            'variant_id'          => 'Variant ID',
            'variant_sku'         => 'Variant SKU',
            'variant_name'        => 'Variant Name',
            'shade_or_color'      => 'Shade / Color',
            'color_hex'           => 'Color Hex Code',
            'size_or_packing'     => 'Size / Box Packing',
            'variant_status'      => 'Variant Status',
            'dynamic_attributes'  => 'Dynamic Attributes',
            'seller_sku'          => 'Seller SKU',
            'barcode'             => 'Barcode / GTIN',
            'unit_of_measurement' => 'Unit of Measure',
            'case_pack_quantity'  => 'Case Pack Qty',
            'min_order_quantity'  => 'Min Order Qty (MOQ)',
            'order_multiple'      => 'Order Multiple',
            'allow_unit_orders'   => 'Allow Unit Orders',
            'allow_carton_orders' => 'Allow Carton Orders',
            'listing_status'      => 'Listing Status',
        ];

        if ($canViewPricing) {
            $cols['seller_price']    = 'Seller Price (INR)';
            $cols['commission_rate'] = 'Commission Rate (%)';
            $cols['selling_price']   = 'Selling Price (INR)';
            $cols['customer_mrp']    = 'Customer MRP (INR)';
        }

        if ($canViewInventory) {
            $cols['stock_quantity']      = 'Stock Quantity';
            $cols['reserved_stock']      = 'Reserved Stock';
            $cols['available_stock']     = 'Available Stock';
            $cols['low_stock_threshold'] = 'Low Stock Threshold';
            $cols['dispatch_lead_days']  = 'Dispatch Lead Days';
        }

        $cols['created_at'] = 'Listing Created Date';
        $cols['updated_at'] = 'Listing Updated Date';

        return $cols;
    }

    /**
     * Prevent CSV / Formula Injection (CWE-1236) by prepending a single quote
     * to any value that begins with formula trigger characters.
     */
    public static function sanitizeCellValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        $str = (string) $value;
        if ($str === '') {
            return '';
        }
        $firstChar = $str[0];
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r", '%'], true)) {
            return "'" . $str;
        }
        return $str;
    }

    /**
     * Convert zero-based numeric index to Excel column letters (0 -> A, 25 -> Z, 26 -> AA).
     */
    public function indexToColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(($index % 26) + 65) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }

    /**
     * Return secure authenticated file download response.
     */
    public function getDownloadResponse(SellerCatalogueExport $export, User $user): BinaryFileResponse
    {
        // Enforce Phase 2.1 authorization & ownership
        if (!$user->isAdmin()) {
            $userCompany = $user->resolveSellerCompany();
            if (!$userCompany || $userCompany->id !== $export->company_id) {
                abort(404, 'Export not found or unauthorized.');
            }
            if (!$user->hasSellerPermission('excel.export')) {
                abort(403, 'Permission denied: excel.export required to download export files.');
            }
        }

        if (!$export->isCompleted() || !$export->file_path) {
            abort(400, 'Export has not completed yet or file is missing.');
        }

        $fullPath = storage_path('app/' . $export->file_path);
        if (!file_exists($fullPath)) {
            abort(404, 'The requested export file was not found on the server.');
        }

        $mimeType = $export->export_format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->download($fullPath, $export->file_name, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $export->file_name . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Validate that all selected product IDs belong to the company's catalogue listings.
     */
    protected function validateProductOwnership(Company $company, array $productIds): void
    {
        $ownedCount = SellerListing::where('company_id', $company->id)
            ->whereIn('product_id', $productIds)
            ->distinct('product_id')
            ->count('product_id');

        $uniqueRequested = count(array_unique($productIds));

        if ($ownedCount < $uniqueRequested) {
            throw ValidationException::withMessages([
                'product_ids' => 'One or more requested products do not belong to your seller account.',
            ]);
        }
    }

    /**
     * Clean up export files older than specified days.
     */
    public function cleanupOldExports(int $daysToKeep = 7): int
    {
        $threshold = now()->subDays($daysToKeep);
        $oldExports = SellerCatalogueExport::where('created_at', '<', $threshold)->get();

        $deletedCount = 0;
        foreach ($oldExports as $exp) {
            if ($exp->file_path) {
                $fullPath = storage_path('app/' . $exp->file_path);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $exp->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }
}

