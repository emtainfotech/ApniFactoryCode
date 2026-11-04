<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\SellerListing;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    /**
     * Validate packaging, MOQ and order multiple constraints for a listing.
     *
     * @param SellerListing $listing
     * @param int $quantity
     * @return void
     * @throws InvalidArgumentException
     */
    public function validatePackagingRules(SellerListing $listing, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Order quantity must be a positive integer.');
        }

        $moq = $listing->min_order_quantity ?: 1;
        if ($quantity < $moq) {
            throw new InvalidArgumentException("Minimum order quantity for SKU {$listing->seller_sku} is {$moq}.");
        }

        $orderMultiple = $listing->order_multiple ?: 1;
        if ($quantity % $orderMultiple !== 0) {
            throw new InvalidArgumentException("Order quantity must be in multiples of {$orderMultiple} for SKU {$listing->seller_sku}.");
        }

        if (!$listing->allow_unit_orders && $listing->allow_carton_orders && $listing->case_pack_quantity > 0) {
            if ($quantity % $listing->case_pack_quantity !== 0) {
                throw new InvalidArgumentException("This item can only be ordered in complete cartons of {$listing->case_pack_quantity} units.");
            }
        }
    }

    /**
     * Reserve stock for a pending checkout with pessimistic locking.
     *
     * @param int $listingId
     * @param int $quantity
     * @param string $orderNo
     * @param int|null $userId
     * @return SellerListing
     */
    public function reserveStock(int $listingId, int $quantity, string $orderNo, ?int $userId = null): SellerListing
    {
        return DB::transaction(function () use ($listingId, $quantity, $orderNo, $userId) {
            /** @var SellerListing $listing */
            $listing = SellerListing::lockForUpdate()->findOrFail($listingId);

            $this->validatePackagingRules($listing, $quantity);

            $available = $listing->available_stock;
            if ($available < $quantity) {
                throw new RuntimeException("Insufficient stock for SKU {$listing->seller_sku}. Available: {$available}, Requested: {$quantity}");
            }

            $prevStock = $listing->stock_quantity;
            $listing->reserved_stock = ($listing->reserved_stock ?? 0) + $quantity;
            $listing->save();

            InventoryMovement::create([
                'seller_listing_id' => $listing->id,
                'seller_user_id' => $listing->seller_user_id,
                'movement_type' => 'order_reserve',
                'quantity_change' => -$quantity,
                'previous_stock' => $prevStock,
                'new_stock' => $listing->stock_quantity,
                'reference_order_no' => $orderNo,
                'notes' => "Reserved {$quantity} units for order {$orderNo}",
                'created_by' => $userId,
            ]);

            return $listing;
        });
    }

    /**
     * Deduct stock upon order confirmation.
     *
     * @param int $listingId
     * @param int $quantity
     * @param string $orderNo
     * @param int|null $userId
     * @return SellerListing
     */
    public function deductStock(int $listingId, int $quantity, string $orderNo, ?int $userId = null): SellerListing
    {
        return DB::transaction(function () use ($listingId, $quantity, $orderNo, $userId) {
            /** @var SellerListing $listing */
            $listing = SellerListing::lockForUpdate()->findOrFail($listingId);

            $prevStock = $listing->stock_quantity;
            $newStock = max(0, $listing->stock_quantity - $quantity);

            $listing->stock_quantity = $newStock;
            $listing->reserved_stock = max(0, ($listing->reserved_stock ?? 0) - $quantity);
            $listing->save();

            InventoryMovement::create([
                'seller_listing_id' => $listing->id,
                'seller_user_id' => $listing->seller_user_id,
                'movement_type' => 'order_deduct',
                'quantity_change' => -$quantity,
                'previous_stock' => $prevStock,
                'new_stock' => $newStock,
                'reference_order_no' => $orderNo,
                'notes' => "Deducted {$quantity} units for order {$orderNo}",
                'created_by' => $userId,
            ]);

            return $listing;
        });
    }

    /**
     * Release reserved stock on order cancellation or checkout timeout.
     *
     * @param int $listingId
     * @param int $quantity
     * @param string $orderNo
     * @param int|null $userId
     * @return SellerListing
     */
    public function releaseStock(int $listingId, int $quantity, string $orderNo, ?int $userId = null): SellerListing
    {
        return DB::transaction(function () use ($listingId, $quantity, $orderNo, $userId) {
            /** @var SellerListing $listing */
            $listing = SellerListing::lockForUpdate()->findOrFail($listingId);

            $prevStock = $listing->stock_quantity;
            $listing->reserved_stock = max(0, ($listing->reserved_stock ?? 0) - $quantity);
            $listing->save();

            InventoryMovement::create([
                'seller_listing_id' => $listing->id,
                'seller_user_id' => $listing->seller_user_id,
                'movement_type' => 'order_cancel_release',
                'quantity_change' => $quantity,
                'previous_stock' => $prevStock,
                'new_stock' => $listing->stock_quantity,
                'reference_order_no' => $orderNo,
                'notes' => "Released {$quantity} reserved units from order {$orderNo}",
                'created_by' => $userId,
            ]);

            return $listing;
        });
    }

    /**
     * Seller-initiated stock adjustment with full audit trail.
     *
     * @param int $listingId
     * @param int $newStock
     * @param string $reason
     * @param int $userId
     * @return SellerListing
     */
    public function adjustStock(int $listingId, int $newStock, string $reason, int $userId): SellerListing
    {
        if ($newStock < 0) {
            throw new InvalidArgumentException('Stock quantity cannot be negative.');
        }

        return DB::transaction(function () use ($listingId, $newStock, $reason, $userId) {
            /** @var SellerListing $listing */
            $listing = SellerListing::lockForUpdate()->findOrFail($listingId);

            $prevStock = $listing->stock_quantity;
            $delta = $newStock - $prevStock;

            $listing->stock_quantity = $newStock;
            $listing->save();

            InventoryMovement::create([
                'seller_listing_id' => $listing->id,
                'seller_user_id' => $listing->seller_user_id,
                'movement_type' => 'manual_adjustment',
                'quantity_change' => $delta,
                'previous_stock' => $prevStock,
                'new_stock' => $newStock,
                'notes' => $reason,
                'created_by' => $userId,
            ]);

            return $listing;
        });
    }
}
