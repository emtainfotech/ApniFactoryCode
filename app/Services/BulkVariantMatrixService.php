<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkVariantMatrixService
{
    public function __construct(
        protected BuyerCartValidationService $cartService,
        protected DynamicPriceRevisionService $pricingService
    ) {
    }

    /**
     * Retrieve the matrix of compatible variants and listings for a product.
     */
    public function getVariantMatrixForProduct(int $productId, ?int $sellerCompanyId = null, ?int $buyerId = null): array
    {
        $product = Product::with(['category', 'subcategory', 'brand', 'variants.shadeCard', 'variants.boxPacking'])
            ->findOrFail($productId);

        $listingsQuery = SellerListing::with(['company', 'variant.shadeCard', 'variant.boxPacking'])
            ->where('product_id', $productId)
            ->where('status', 'Active');

        if ($sellerCompanyId) {
            $listingsQuery->where('company_id', $sellerCompanyId);
        }

        $listings = $listingsQuery->get();

        $matrixRows = [];
        foreach ($listings as $listing) {
            $variant = $listing->variant;
            $company = $listing->company;

            // Resolve effective unit price
            $priceResolution = $this->pricingService->resolveCurrentEffectivePrice(
                $listing,
                $buyerId,
                max(1, (int)$listing->min_order_quantity),
                'seller_price'
            );

            $matrixRows[] = [
                'seller_listing_id'   => $listing->id,
                'company_id'          => $listing->company_id,
                'company_name'        => $company ? $company->name : 'N/A',
                'seller_sku'          => $listing->seller_sku,
                'variant_id'          => $variant ? $variant->id : null,
                'variant_name'        => $variant ? $variant->variant_name : 'Standard',
                'shade_id'            => $variant && $variant->shadeCard ? $variant->shadeCard->id : null,
                'shade_name'          => $variant && $variant->shadeCard ? $variant->shadeCard->name : 'Standard',
                'shade_color_code'    => $variant && $variant->shadeCard ? $variant->shadeCard->color_code : null,
                'pack_litres'         => $variant ? (float)$variant->pack_litres : null,
                'unit_of_measurement' => $listing->unit_of_measurement ?? 'Unit',
                'case_pack_quantity'  => (int)$listing->case_pack_quantity,
                'order_multiple'      => (int)$listing->order_multiple,
                'min_order_quantity'  => (int)$listing->min_order_quantity,
                'effective_multiple'  => $listing->getEffectiveOrderMultiple(),
                'stock_quantity'      => (int)$listing->stock_quantity,
                'available_stock'     => $listing->available_stock,
                'resolved_unit_price' => (float)$priceResolution['effective_price'],
                'mrp'                 => (float)$listing->mrp,
                'status'              => $listing->status,
            ];
        }

        return [
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'tax_rate'     => (float)$product->tax,
            'matrix_rows'  => $matrixRows,
        ];
    }

    /**
     * Submit bulk quantity matrix selections.
     * Validates each selected variant row independently.
     */
    public function submitBulkMatrix(int $customerId, array $items): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['At least one SKU selection is required.'],
            ]);
        }

        $rowErrors = [];
        $validatedItems = [];

        foreach ($items as $index => $item) {
            $listingId = $item['seller_listing_id'] ?? null;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;

            if (!$listingId) {
                $rowErrors[$index] = "Item at index {$index} is missing 'seller_listing_id'.";
                continue;
            }

            if ($quantity <= 0) {
                continue; // Skip zero/empty quantities in bulk matrix
            }

            $listing = SellerListing::with(['company', 'product', 'variant'])->find($listingId);
            if (!$listing) {
                $rowErrors[$index] = "SKU with ID {$listingId} not found.";
                continue;
            }

            $validation = $this->cartService->validateItem($listing, $quantity, $customerId);
            if (!$validation['valid']) {
                $rowErrors[$index] = implode(' ', $validation['errors']);
                continue;
            }

            $validatedItems[] = [
                'listing'   => $listing,
                'quantity'  => $quantity,
                'unitPrice' => $validation['resolved_unit_price'],
            ];
        }

        if (!empty($rowErrors)) {
            return [
                'success'    => false,
                'message'    => 'One or more items in the bulk matrix failed validation.',
                'row_errors' => $rowErrors,
            ];
        }

        if (empty($validatedItems)) {
            throw ValidationException::withMessages([
                'items' => ['No valid quantities were provided in the matrix.'],
            ]);
        }

        // Apply all validated items atomically
        return DB::transaction(function () use ($customerId, $validatedItems) {
            foreach ($validatedItems as $entry) {
                $listing = $entry['listing'];
                $quantity = $entry['quantity'];

                $this->cartService->addItemToCart($customerId, $listing->id, $quantity);
            }

            $updatedCart = $this->cartService->getGroupedCart($customerId);

            return [
                'success'      => true,
                'message'      => 'Bulk items successfully added to cart.',
                'added_count'  => count($validatedItems),
                'cart_summary' => $updatedCart,
            ];
        });
    }
}
