<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerListing extends Model
{
    use HasFactory;

    protected $table = 'seller_listings';

    protected $fillable = [
        'seller_user_id',
        'company_id',
        'product_id',
        'product_variant_id',
        'unit_of_measurement',
        'case_pack_quantity',
        'seller_sku',
        'barcode',
        'seller_price',
        'commission_rate',
        'selling_price',
        'mrp',
        'min_order_quantity',
        'order_multiple',
        'allow_unit_orders',
        'allow_carton_orders',
        'stock_quantity',
        'reserved_stock',
        'low_stock_threshold',
        'dispatch_lead_days',
        'status',
    ];

    protected $casts = [
        'seller_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'min_order_quantity' => 'integer',
        'case_pack_quantity' => 'integer',
        'order_multiple' => 'integer',
        'stock_quantity' => 'integer',
        'reserved_stock' => 'integer',
        'allow_unit_orders' => 'boolean',
        'allow_carton_orders' => 'boolean',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'seller_listing_id');
    }

    /**
     * Compute customer-facing selling price from factory wholesale price and margin
     */
    public static function calculateSellingPrice(float $sellerPrice, float $commissionRate): float
    {
        return round($sellerPrice * (1 + ($commissionRate / 100)), 2);
    }

    /**
     * Get net available unreserved stock
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, (int)$this->stock_quantity - (int)$this->reserved_stock);
    }
}
