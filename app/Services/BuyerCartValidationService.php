<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartAttribute;
use App\Models\Company;
use App\Models\Product;
use App\Models\SellerListing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BuyerCartValidationService
{
    public function __construct(
        protected DynamicPriceRevisionService $pricingService
    ) {
    }

    /**
     * Validate a prospective order item quantity against SKU packaging rules, MOQ, and stock.
     */
    public function validateItem(
        SellerListing $listing,
        int $quantity,
        ?int $buyerId = null
    ): array {
        $listing->loadMissing(['company', 'product', 'variant']);

        $errors = [];
        $warnings = [];

        // 1. Seller & Listing Status
        if ($listing->status !== 'Active') {
            $errors[] = "SKU '{$listing->seller_sku}' is currently {$listing->status} and cannot be ordered.";
        }

        if ($listing->company && $listing->company->status !== 'Active') {
            $errors[] = "Manufacturer '{$listing->company->name}' is currently inactive.";
        }

        // 2. Quantity validation (MOQ, Multiple, Stock)
        $qtyValidation = $listing->validateOrderQuantity($quantity);
        if (!$qtyValidation['valid']) {
            $errors = array_merge($errors, $qtyValidation['errors']);
        }

        // 3. Authoritative Price Resolution
        $priceResolution = $this->pricingService->resolveCurrentEffectivePrice(
            $listing,
            $buyerId,
            max(1, $quantity),
            'seller_price'
        );
        $resolvedUnitPrice = (float)$priceResolution['effective_price'];

        return [
            'valid'               => empty($errors),
            'errors'              => $errors,
            'warnings'            => $warnings,
            'resolved_unit_price' => $resolvedUnitPrice,
            'price_tier'          => $priceResolution['price_tier'] ?? 'catalogue_base',
            'next_valid_quantity' => $qtyValidation['next_valid_quantity'] ?? 1,
            'moq'                 => $qtyValidation['moq'] ?? 1,
            'order_multiple'      => $qtyValidation['order_multiple'] ?? 1,
            'available_stock'     => $qtyValidation['available_stock'] ?? 0,
            'unit_of_measurement'=> $listing->unit_of_measurement ?? 'Unit',
        ];
    }

    /**
     * Add a SellerListing item to the buyer's cart with validation.
     */
    public function addItemToCart(
        int $customerId,
        int $sellerListingId,
        int $quantity,
        ?int $addressId = null
    ): array {
        return DB::transaction(function () use ($customerId, $sellerListingId, $quantity, $addressId) {
            $listing = SellerListing::with(['company', 'product', 'variant'])->findOrFail($sellerListingId);

            // Run validation
            $validation = $this->validateItem($listing, $quantity, $customerId);
            if (!$validation['valid']) {
                throw ValidationException::withMessages([
                    'quantity' => $validation['errors'],
                ]);
            }

            $product = $listing->product;
            $company = $listing->company;
            $unitPrice = $validation['resolved_unit_price'];

            // 1. Find or create parent Cart group for this customer and product
            $cart = Cart::firstOrCreate(
                [
                    'customer_id' => $customerId,
                    'product_id'  => $product ? $product->id : null,
                    'company_id'  => $company ? $company->id : null,
                ],
                [
                    'brand_id'    => $product ? $product->brand_id : null,
                    'category_id' => $product ? $product->category_id : null,
                    'productname' => $product ? $product->name : 'Product',
                    'addressid'   => $addressId ?: 0,
                ]
            );

            // 2. Find or create CartAttribute for this SKU
            $cartItem = CartAttribute::where('cart_id', $cart->id)
                ->where('seller_listing_id', $listing->id)
                ->first();

            $totalQty = $cartItem ? ($cartItem->qty + $quantity) : $quantity;

            // Re-validate combined quantity
            $combinedValidation = $this->validateItem($listing, $totalQty, $customerId);
            if (!$combinedValidation['valid']) {
                throw ValidationException::withMessages([
                    'quantity' => $combinedValidation['errors'],
                ]);
            }

            $totalPrice = round($totalQty * $unitPrice, 2);
            $taxRate = (float)($product ? $product->tax : 0.0);
            $taxAmount = round(($totalPrice * $taxRate) / 100, 2);

            if ($cartItem) {
                $cartItem->update([
                    'qty'        => $totalQty,
                    'unitprice'  => $unitPrice,
                    'totalprice' => $totalPrice,
                    'prprice'    => (float)$listing->seller_price,
                    'tax'        => $taxRate,
                    'taxamount'  => $taxAmount,
                ]);
            } else {
                $cartItem = CartAttribute::create([
                    'customer_id'       => $customerId,
                    'cart_id'           => $cart->id,
                    'seller_listing_id' => $listing->id,
                    'qty'               => $totalQty,
                    'boxpcs'            => max(1, (int)$listing->case_pack_quantity),
                    'unitprice'         => $unitPrice,
                    'totalprice'        => $totalPrice,
                    'prprice'           => (float)$listing->seller_price,
                    'tax'               => $taxRate,
                    'taxamount'         => $taxAmount,
                    'coupon'            => 0.00,
                ]);
            }

            return $this->getGroupedCart($customerId);
        });
    }

    /**
     * Update quantity of an individual cart item.
     */
    public function updateItemQuantity(
        int $customerId,
        int $cartAttributeId,
        int $newQuantity
    ): array {
        return DB::transaction(function () use ($customerId, $cartAttributeId, $newQuantity) {
            $cartItem = CartAttribute::with(['cart.product', 'cart.company', 'sellerListing'])
                ->where('id', $cartAttributeId)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            // If quantity is 0 or less, remove item
            if ($newQuantity <= 0) {
                return $this->removeItemFromCart($customerId, $cartAttributeId);
            }

            $listing = $cartItem->sellerListing;
            if ($listing) {
                $validation = $this->validateItem($listing, $newQuantity, $customerId);
                if (!$validation['valid']) {
                    throw ValidationException::withMessages([
                        'quantity' => $validation['errors'],
                    ]);
                }
                $unitPrice = $validation['resolved_unit_price'];
            } else {
                $unitPrice = (float)$cartItem->unitprice;
            }

            $totalPrice = round($newQuantity * $unitPrice, 2);
            $taxRate = (float)$cartItem->tax;
            $taxAmount = round(($totalPrice * $taxRate) / 100, 2);

            $cartItem->update([
                'qty'        => $newQuantity,
                'unitprice'  => $unitPrice,
                'totalprice' => $totalPrice,
                'taxamount'  => $taxAmount,
            ]);

            return $this->getGroupedCart($customerId);
        });
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItemFromCart(int $customerId, int $cartAttributeId): array
    {
        return DB::transaction(function () use ($customerId, $cartAttributeId) {
            $cartItem = CartAttribute::where('id', $cartAttributeId)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            $cartId = $cartItem->cart_id;
            $cartItem->delete();

            // If no more items in this Cart record, clean up parent Cart
            $remaining = CartAttribute::where('cart_id', $cartId)->count();
            if ($remaining === 0) {
                Cart::where('id', $cartId)->where('customer_id', $customerId)->delete();
            }

            return $this->getGroupedCart($customerId);
        });
    }

    /**
     * Empty entire cart for a customer.
     */
    public function clearCart(int $customerId): array
    {
        return DB::transaction(function () use ($customerId) {
            CartAttribute::where('customer_id', $customerId)->delete();
            Cart::where('customer_id', $customerId)->delete();

            return $this->getGroupedCart($customerId);
        });
    }

    /**
     * Retrieve the buyer cart fully grouped by seller/manufacturer, with MOV evaluation.
     */
    public function getGroupedCart(int $customerId, ?int $buyerId = null): array
    {
        $carts = Cart::with([
            'company',
            'product',
            'attributes.sellerListing.variant',
            'attributes.sellerListing.product',
            'attributes.productAttribute',
        ])
        ->where('customer_id', $customerId)
        ->get();

        $sellerGroups = [];
        $overallGrandTotal = 0.0;
        $overallTaxTotal = 0.0;
        $overallItemCount = 0;
        $allSellersCanCheckout = true;

        // Group cart instances by company_id
        $groupedByCompany = $carts->groupBy(function ($cart) {
            return $cart->company_id ?: 0;
        });

        foreach ($groupedByCompany as $companyId => $companyCarts) {
            $company = $companyCarts->first()->company ?: Company::find($companyId);
            $companyName = $company ? $company->name : 'Unknown Manufacturer';
            $companyStatus = $company ? $company->status : 'Active';

            $groupItems = [];
            $sellerSubtotal = 0.0;
            $sellerTax = 0.0;
            $sellerTotalQuantity = 0;
            $sellerHasValidationErrors = false;

            foreach ($companyCarts as $c) {
                foreach ($c->attributes as $item) {
                    $listing = $item->sellerListing;
                    $product = $c->product ?: ($listing ? $listing->product : null);
                    $variant = $listing ? $listing->variant : null;

                    // Revalidate live
                    $itemValidation = $listing
                        ? $this->validateItem($listing, (int)$item->qty, $customerId)
                        : ['valid' => true, 'errors' => [], 'resolved_unit_price' => (float)$item->unitprice];

                    if (!$itemValidation['valid']) {
                        $sellerHasValidationErrors = true;
                    }

                    $resolvedPrice = (float)$itemValidation['resolved_unit_price'];
                    $lineTotal = round((int)$item->qty * $resolvedPrice, 2);
                    $lineTax = round(($lineTotal * (float)$item->tax) / 100, 2);

                    $sellerSubtotal += $lineTotal;
                    $sellerTax += $lineTax;
                    $sellerTotalQuantity += (int)$item->qty;
                    $overallItemCount++;

                    $groupItems[] = [
                        'cart_attribute_id'    => $item->id,
                        'cart_id'              => $c->id,
                        'seller_listing_id'    => $listing ? $listing->id : null,
                        'seller_sku'           => $listing ? $listing->seller_sku : $item->effective_sku,
                        'product_id'           => $product ? $product->id : $c->product_id,
                        'product_name'         => $product ? $product->name : $c->productname,
                        'product_image'        => $product ? $product->image : null,
                        'variant_id'           => $variant ? $variant->id : null,
                        'variant_name'         => $variant ? $variant->variant_name : 'Standard',
                        'pack_litres'          => $variant ? (float)$variant->pack_litres : null,
                        'unit_of_measurement'  => $listing ? $listing->unit_of_measurement : 'Unit',
                        'case_pack_quantity'   => $listing ? (int)$listing->case_pack_quantity : (int)$item->boxpcs,
                        'order_multiple'       => $listing ? (int)$listing->order_multiple : 1,
                        'min_order_quantity'   => $listing ? (int)$listing->min_order_quantity : 1,
                        'available_stock'      => $listing ? $listing->available_stock : 999,
                        'quantity'             => (int)$item->qty,
                        'stored_unit_price'    => (float)$item->unitprice,
                        'resolved_unit_price'  => $resolvedPrice,
                        'total_price'          => $lineTotal,
                        'tax_rate'             => (float)$item->tax,
                        'tax_amount'           => $lineTax,
                        'is_valid'             => $itemValidation['valid'],
                        'validation_errors'    => $itemValidation['errors'] ?? [],
                        'next_valid_quantity'  => $itemValidation['next_valid_quantity'] ?? (int)$item->qty,
                    ];
                }
            }

            $sellerSubtotal = round($sellerSubtotal, 2);
            $sellerTax = round($sellerTax, 2);
            $configuredMov = $company ? $company->getRawMinOrderValue() : 0.0;
            $shortfall = $company ? $company->getMovShortfall($sellerSubtotal) : 0.0;
            $progress = $company ? $company->getMovProgress($sellerSubtotal) : 100.0;
            $isMovMet = $company ? $company->isMovSatisfied($sellerSubtotal) : true;
            $isSellerActive = ($companyStatus === 'Active' || $companyStatus === 1 || $companyStatus === '1');

            $movStatus = $configuredMov <= 0
                ? 'not_configured'
                : ($isMovMet ? 'met' : 'shortfall');

            $canProceed = $isMovMet && !$sellerHasValidationErrors && $isSellerActive;
            if (!$canProceed && count($groupItems) > 0) {
                $allSellersCanCheckout = false;
            }

            // Freight calculation: maintained separately per Section 6
            $freightEstimate = 0.00;

            $sellerGroups[] = [
                'company_id'             => (int)$companyId,
                'company_name'           => $companyName,
                'company_status'         => $companyStatus,
                'is_seller_active'       => $isSellerActive,
                'items_count'            => count($groupItems),
                'total_quantity'         => $sellerTotalQuantity,
                'seller_subtotal'        => $sellerSubtotal,
                'seller_tax'             => $sellerTax,
                'seller_grand_total'     => round($sellerSubtotal + $sellerTax, 2),
                'freight_amount'         => $freightEstimate,
                'is_freight_separate'    => true,
                'configured_mov'         => $configuredMov,
                'mov_shortfall'          => $shortfall,
                'mov_progress_percentage'=> $progress,
                'mov_status'             => $movStatus,
                'is_mov_satisfied'       => $isMovMet,
                'can_checkout'           => $canProceed,
                'items'                  => $groupItems,
            ];

            $overallGrandTotal += ($sellerSubtotal + $sellerTax);
            $overallTaxTotal += $sellerTax;
        }

        $overallGrandTotal = round($overallGrandTotal, 2);

        return [
            'status'                   => true,
            'customer_id'              => $customerId,
            'overall_grand_total'      => $overallGrandTotal,
            'overall_tax_total'        => round($overallTaxTotal, 2),
            'total_items_count'        => $overallItemCount,
            'total_sellers_count'      => count($sellerGroups),
            'all_sellers_can_checkout' => !empty($sellerGroups) && $allSellersCanCheckout,
            'seller_groups'            => $sellerGroups,
        ];
    }

    /**
     * Full pre-transaction cart revalidation service (Section 8).
     * Detects changes in price revisions, stock levels, packaging rules, and seller eligibility.
     */
    public function revalidateCart(int $customerId): array
    {
        $cartData = $this->getGroupedCart($customerId);

        $priceChanges = [];
        $stockIssues = [];
        $ruleIssues = [];
        $movIssues = [];
        $sellerIssues = [];
        $hasChanges = false;

        foreach ($cartData['seller_groups'] as $group) {
            if (!$group['is_seller_active']) {
                $sellerIssues[] = "Manufacturer '{$group['company_name']}' is no longer active.";
                $hasChanges = true;
            }

            if (!$group['is_mov_satisfied'] && $group['configured_mov'] > 0) {
                $movIssues[] = [
                    'company_id'      => $group['company_id'],
                    'company_name'    => $group['company_name'],
                    'configured_mov'  => $group['configured_mov'],
                    'current_subtotal'=> $group['seller_subtotal'],
                    'shortfall'       => $group['mov_shortfall'],
                    'message'         => "Shortfall of ₹{$group['mov_shortfall']} to meet Minimum Order Value of ₹{$group['configured_mov']}.",
                ];
            }

            foreach ($group['items'] as $item) {
                // Check price drift
                if (abs($item['stored_unit_price'] - $item['resolved_unit_price']) > 0.001) {
                    $hasChanges = true;
                    $priceChanges[] = [
                        'cart_attribute_id' => $item['cart_attribute_id'],
                        'seller_sku'        => $item['seller_sku'],
                        'old_unit_price'    => $item['stored_unit_price'],
                        'new_unit_price'    => $item['resolved_unit_price'],
                        'price_difference'  => round($item['resolved_unit_price'] - $item['stored_unit_price'], 2),
                        'message'           => "Price for {$item['seller_sku']} changed from ₹{$item['stored_unit_price']} to ₹{$item['resolved_unit_price']}.",
                    ];
                }

                // Check stock insufficiency
                if ($item['quantity'] > $item['available_stock']) {
                    $hasChanges = true;
                    $stockIssues[] = [
                        'cart_attribute_id' => $item['cart_attribute_id'],
                        'seller_sku'        => $item['seller_sku'],
                        'requested_quantity'=> $item['quantity'],
                        'available_stock'   => $item['available_stock'],
                        'message'           => "Available stock for {$item['seller_sku']} is only {$item['available_stock']} (requested {$item['quantity']}).",
                    ];
                }

                // Check MOQ / Multiple errors
                if (!$item['is_valid']) {
                    $hasChanges = true;
                    $ruleIssues[] = [
                        'cart_attribute_id' => $item['cart_attribute_id'],
                        'seller_sku'        => $item['seller_sku'],
                        'errors'            => $item['validation_errors'],
                    ];
                }
            }
        }

        $isCheckoutReady = empty($priceChanges) &&
            empty($stockIssues) &&
            empty($ruleIssues) &&
            empty($movIssues) &&
            empty($sellerIssues) &&
            $cartData['total_items_count'] > 0;

        return [
            'is_checkout_ready' => $isCheckoutReady,
            'has_changes'       => $hasChanges,
            'price_changes'     => $priceChanges,
            'stock_issues'      => $stockIssues,
            'rule_issues'       => $ruleIssues,
            'mov_issues'        => $movIssues,
            'seller_issues'     => $sellerIssues,
            'cart_summary'      => $cartData,
        ];
    }
}
