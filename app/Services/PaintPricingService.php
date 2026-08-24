<?php

namespace App\Services;

use App\Models\BoxPacking;
use App\Models\Company;
use App\Models\PaintPriceAdjustment;
use App\Models\Product;
use App\Models\ProductAttributes;
use App\Models\ShadeCard;
use Illuminate\Support\Facades\DB;

class PaintPricingService
{
    /**
     * Parse pack size to litre float value (e.g. '1 L' => 1.0, '4 Ltr' => 4.0, '500 ML' => 0.5, '20 Litres' => 20.0).
     * Falls back to pcs or 1.0.
     */
    public function parsePackLitres($boxPacking): float
    {
        if (is_numeric($boxPacking)) {
            $packing = BoxPacking::find($boxPacking);
        } elseif ($boxPacking instanceof BoxPacking) {
            $packing = $boxPacking;
        } else {
            $packing = null;
        }

        if (!$packing) {
            return 1.00;
        }

        $name = trim($packing->name);

        // Check for Millilitres (ML)
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:ml|millilitre|milliliter)/i', $name, $matches)) {
            return round((float)$matches[1] / 1000, 3);
        }

        // Check for Litres (L / Ltr / Litre / Liter / Litres)
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:l|ltr|ltrs|liter|litre|liters|litres)/i', $name, $matches)) {
            return (float)$matches[1];
        }

        // Check for Kilograms / Grams if applicable
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:kg|kilo|kilogram)/i', $name, $matches)) {
            return (float)$matches[1];
        }
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:gm|g|gram)/i', $name, $matches)) {
            return round((float)$matches[1] / 1000, 3);
        }

        // Fallback to pcs column if positive
        if (!empty($packing->pcs) && $packing->pcs > 0) {
            return (float)$packing->pcs;
        }

        return 1.00;
    }

    /**
     * Get effective commission rate for a seller / product (default 25%).
     */
    public function getEffectiveCommissionRate($sellerId = null, $productId = null): float
    {
        if ($sellerId) {
            $company = Company::where('user_id', $sellerId)->first();
            if ($company && !empty($company->comission) && (float)$company->comission > 0) {
                return (float)$company->comission;
            }
        }

        if ($productId) {
            $product = Product::find($productId);
            if ($product && !empty($product->user_id)) {
                $company = Company::where('user_id', $product->user_id)->first();
                if ($company && !empty($company->comission) && (float)$company->comission > 0) {
                    return (float)$company->comission;
                }
            }
        }

        return 25.00; // Standard default 25%
    }

    /**
     * Query SKUs matching the provided scope.
     *
     * $scope format:
     * [
     *   'type' => 'family'|'shades'|'packings'|'skus',
     *   'shades' => [1, 2, ...],
     *   'packings' => [1, 2, ...],
     *   'skus' => [101, 102, ...]
     * ]
     */
    public function getScopedSkus(int $productId, array $scope = [])
    {
        $query = ProductAttributes::where('product_id', $productId);

        $scopeType = $scope['type'] ?? 'family';

        if ($scopeType === 'shades' && !empty($scope['shades'])) {
            $query->whereIn('color', (array)$scope['shades']);
        } elseif ($scopeType === 'packings' && !empty($scope['packings'])) {
            $query->whereIn('quantity', (array)$scope['packings']);
        } elseif ($scopeType === 'skus' && !empty($scope['skus'])) {
            $query->whereIn('id', (array)$scope['skus']);
        }

        return $query->get();
    }

    /**
     * Calculate non-persisted preview for proposed price adjustments.
     */
    public function calculatePreview(
        int $productId,
        string $adjustmentType,
        float $adjustmentValue,
        array $scope = []
    ): array {
        $skus = $this->getScopedSkus($productId, $scope);
        $product = Product::findOrFail($productId);
        $commissionRate = $this->getEffectiveCommissionRate($product->user_id, $productId);

        $previewItems = [];
        $totalOldSellerPrice = 0;
        $totalNewSellerPrice = 0;

        foreach ($skus as $sku) {
            $packLitres = $sku->pack_litres && (float)$sku->pack_litres > 0
                ? (float)$sku->pack_litres
                : $this->parsePackLitres($sku->quantity);

            // Seller factory price (fallback to price / 1.25 if seller_price not set)
            $currentSellerPrice = $sku->seller_price !== null && (float)$sku->seller_price > 0
                ? (float)$sku->seller_price
                : ((float)$sku->price > 0 ? round((float)$sku->price / (1 + ($commissionRate / 100)), 2) : 0);

            $currentCustomerPrice = (float)$sku->price > 0
                ? (float)$sku->price
                : round($currentSellerPrice * (1 + ($commissionRate / 100)), 2);

            $newSellerPrice = $currentSellerPrice;
            $appliedDelta = 0;

            switch (strtolower($adjustmentType)) {
                case 'per_litre':
                case 'per_liter':
                    $appliedDelta = $adjustmentValue * $packLitres;
                    $newSellerPrice = max(0, $currentSellerPrice + $appliedDelta);
                    break;

                case 'percentage':
                    $appliedDelta = $currentSellerPrice * ($adjustmentValue / 100);
                    $newSellerPrice = max(0, $currentSellerPrice + $appliedDelta);
                    break;

                case 'fixed':
                    $appliedDelta = $adjustmentValue;
                    $newSellerPrice = max(0, $currentSellerPrice + $appliedDelta);
                    break;
            }

            $newSellerPrice = round($newSellerPrice, 2);
            $newCustomerPrice = round($newSellerPrice * (1 + ($commissionRate / 100)), 2);

            $shade = ShadeCard::find($sku->color);
            $packing = BoxPacking::find($sku->quantity);

            $previewItems[] = [
                'sku_id'                 => $sku->id,
                'shade_id'               => $sku->color,
                'shade_name'             => $shade ? $shade->name : ('Shade #' . $sku->color),
                'hexcode'                => $shade ? $shade->hexcode : '#CCCCCC',
                'packing_id'             => $sku->quantity,
                'packing_name'           => $packing ? $packing->name : ('Pack #' . $sku->quantity),
                'pack_litres'            => $packLitres,
                'old_seller_price'       => $currentSellerPrice,
                'new_seller_price'       => $newSellerPrice,
                'adjustment_delta'       => round($newSellerPrice - $currentSellerPrice, 2),
                'old_customer_price'     => $currentCustomerPrice,
                'new_customer_price'     => $newCustomerPrice,
                'commission_rate'        => $commissionRate,
            ];

            $totalOldSellerPrice += $currentSellerPrice;
            $totalNewSellerPrice += $newSellerPrice;
        }

        return [
            'product_id'             => $productId,
            'product_name'           => $product->name,
            'adjustment_type'        => $adjustmentType,
            'adjustment_value'       => $adjustmentValue,
            'scope'                  => $scope,
            'commission_rate'        => $commissionRate,
            'affected_count'         => count($previewItems),
            'total_old_seller_price' => round($totalOldSellerPrice, 2),
            'total_new_seller_price' => round($totalNewSellerPrice, 2),
            'items'                  => $previewItems,
        ];
    }

    /**
     * Apply confirmed bulk price adjustment atomically inside a DB transaction.
     */
    public function applyAdjustment(
        int $productId,
        string $adjustmentType,
        float $adjustmentValue,
        array $scope = [],
        $userId = null
    ): array {
        $preview = $this->calculatePreview($productId, $adjustmentType, $adjustmentValue, $scope);

        if (empty($preview['items'])) {
            return [
                'success' => false,
                'message' => 'No SKUs matched the selected scope.',
                'affected_count' => 0,
            ];
        }

        return DB::transaction(function () use ($productId, $adjustmentType, $adjustmentValue, $scope, $userId, $preview) {
            foreach ($preview['items'] as $item) {
                ProductAttributes::where('id', $item['sku_id'])->update([
                    'oldprice'        => $item['old_customer_price'],
                    'seller_price'    => $item['new_seller_price'],
                    'price'           => $item['new_customer_price'],
                    'commission_rate' => $item['commission_rate'],
                    'pack_litres'     => $item['pack_litres'],
                ]);
            }

            $audit = PaintPriceAdjustment::create([
                'user_id'          => $userId,
                'product_id'       => $productId,
                'adjustment_type'  => $adjustmentType,
                'adjustment_value' => $adjustmentValue,
                'scope_type'       => $scope['type'] ?? 'family',
                'scope_json'       => $scope,
                'affected_count'   => count($preview['items']),
                'preview_data'     => $preview['items'],
                'created_by'       => $userId,
            ]);

            // Also record in CompanyAuditLog for Company history
            $company = Company::where('user_id', $userId)->first();
            if ($company) {
                \App\Models\CompanyAuditLog::logChange(
                    $company->id,
                    'price_adjustment',
                    "Price Adjustment: {$preview['product_name']}",
                    "Applied " . ($adjustmentType === 'percentage' ? (($adjustmentValue > 0 ? '+' : '') . $adjustmentValue . '%') : ('₹' . number_format($adjustmentValue, 2))) . " (" . ucfirst(str_replace('_', ' ', $adjustmentType)) . ") across " . count($preview['items']) . " SKUs.",
                    ['old_total' => $preview['total_old_seller_price']],
                    ['new_total' => $preview['total_new_seller_price']],
                    $userId
                );
            }

            return [
                'success'        => true,
                'message'        => 'Successfully updated ' . count($preview['items']) . ' SKU prices.',
                'affected_count' => count($preview['items']),
                'audit_id'       => $audit->id,
                'preview'        => $preview,
            ];
        });
    }

    /**
     * Calculate Category-Wise Price Adjustment Preview across all products in a Category.
     */
    public function calculateCategoryPreview(
        int $categoryId,
        string $adjustmentType,
        float $adjustmentValue,
        $userId = null
    ): array {
        $category = \App\Models\Category::find($categoryId);
        $productsQuery = Product::where('category_id', $categoryId)
            ->where(function($q) {
                $q->where('status', '1')
                  ->orWhere('status', 'Active')
                  ->orWhere('status', 'active');
            });

        if ($userId) {
            $productsQuery->where('user_id', $userId);
        }

        $products = $productsQuery->get();

        $allProductPreviews = [];
        $totalAffectedCount = 0;
        $grandOldTotal = 0;
        $grandNewTotal = 0;

        foreach ($products as $prod) {
            $prodPreview = $this->calculatePreview($prod->id, $adjustmentType, $adjustmentValue, ['type' => 'family']);
            if (!empty($prodPreview['items'])) {
                $allProductPreviews[] = $prodPreview;
                $totalAffectedCount += $prodPreview['affected_count'];
                $grandOldTotal += $prodPreview['total_old_seller_price'];
                $grandNewTotal += $prodPreview['total_new_seller_price'];
            }
        }

        return [
            'category_id'            => $categoryId,
            'category_name'          => $category ? $category->name : ('Category #' . $categoryId),
            'adjustment_type'        => $adjustmentType,
            'adjustment_value'       => $adjustmentValue,
            'products_count'         => count($allProductPreviews),
            'affected_count'         => $totalAffectedCount,
            'total_old_seller_price' => round($grandOldTotal, 2),
            'total_new_seller_price' => round($grandNewTotal, 2),
            'product_previews'       => $allProductPreviews,
        ];
    }

    /**
     * Apply Category-Wise Price Adjustment atomically across all products in a Category.
     */
    public function applyCategoryAdjustment(
        int $categoryId,
        string $adjustmentType,
        float $adjustmentValue,
        $userId = null
    ): array {
        $catPreview = $this->calculateCategoryPreview($categoryId, $adjustmentType, $adjustmentValue, $userId);

        if (empty($catPreview['product_previews'])) {
            return [
                'success' => false,
                'message' => 'No active products found in the selected category.',
                'affected_count' => 0,
            ];
        }

        return DB::transaction(function () use ($categoryId, $adjustmentType, $adjustmentValue, $userId, $catPreview) {
            $allFlattenedItems = [];

            foreach ($catPreview['product_previews'] as $prodPreview) {
                foreach ($prodPreview['items'] as $item) {
                    ProductAttributes::where('id', $item['sku_id'])->update([
                        'oldprice'        => $item['old_customer_price'],
                        'seller_price'    => $item['new_seller_price'],
                        'price'           => $item['new_customer_price'],
                        'commission_rate' => $item['commission_rate'],
                        'pack_litres'     => $item['pack_litres'],
                    ]);
                    $allFlattenedItems[] = array_merge($item, ['product_name' => $prodPreview['product_name']]);
                }

                PaintPriceAdjustment::create([
                    'user_id'          => $userId,
                    'product_id'       => $prodPreview['product_id'],
                    'adjustment_type'  => $adjustmentType,
                    'adjustment_value' => $adjustmentValue,
                    'scope_type'       => 'category',
                    'scope_json'       => ['type' => 'category', 'category_id' => $categoryId],
                    'affected_count'   => count($prodPreview['items']),
                    'preview_data'     => $prodPreview['items'],
                    'created_by'       => $userId,
                ]);
            }

            // Log once at Category level in CompanyAuditLog
            $company = Company::where('user_id', $userId)->first();
            if ($company) {
                \App\Models\CompanyAuditLog::logChange(
                    $company->id,
                    'category_price_adjustment',
                    "Category Bulk Adjustment: {$catPreview['category_name']}",
                    "Applied " . ($adjustmentType === 'percentage' ? (($adjustmentValue > 0 ? '+' : '') . $adjustmentValue . '%') : ('₹' . number_format($adjustmentValue, 2))) . " (" . ucfirst(str_replace('_', ' ', $adjustmentType)) . ") across {$catPreview['products_count']} products ({$catPreview['affected_count']} SKUs) in {$catPreview['category_name']}.",
                    ['old_total' => $catPreview['total_old_seller_price']],
                    ['new_total' => $catPreview['total_new_seller_price']],
                    $userId
                );
            }

            return [
                'success'        => true,
                'message'        => "Successfully updated {$catPreview['affected_count']} SKUs across {$catPreview['products_count']} products in {$catPreview['category_name']}.",
                'affected_count' => $catPreview['affected_count'],
                'products_count' => $catPreview['products_count'],
                'preview'        => $catPreview,
            ];
        });
    }
}
