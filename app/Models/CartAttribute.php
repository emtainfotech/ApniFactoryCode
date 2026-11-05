<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartAttribute extends Model
{
    use HasFactory;

    protected $table = 'cartattribute';

    protected $fillable = [
        'customer_id',
        'cart_id',
        'product_attributes_id',
        'seller_listing_id',
        'qty',
        'boxpcs',
        'unitprice',
        'totalprice',
        'prprice',
        'tax',
        'taxamount',
        'coupon',
    ];

    protected $casts = [
        'qty'         => 'integer',
        'boxpcs'      => 'integer',
        'unitprice'   => 'decimal:2',
        'totalprice'  => 'decimal:2',
        'prprice'     => 'decimal:2',
        'tax'         => 'decimal:2',
        'taxamount'   => 'decimal:2',
        'coupon'      => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function sellerListing()
    {
        return $this->belongsTo(SellerListing::class, 'seller_listing_id');
    }

    public function productAttribute()
    {
        return $this->belongsTo(ProductAttributes::class, 'product_attributes_id');
    }

    /**
     * Resolve effective SKU string.
     */
    public function getEffectiveSkuAttribute(): string
    {
        if ($this->sellerListing && $this->sellerListing->seller_sku) {
            return $this->sellerListing->seller_sku;
        }

        if ($this->sellerListing && $this->sellerListing->variant) {
            return $this->sellerListing->variant->variant_sku;
        }

        return 'SKU-' . $this->id;
    }
}
