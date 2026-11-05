<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRevisionItem extends Model
{
    use HasFactory;

    protected $table = 'price_revision_items';

    protected $fillable = [
        'price_revision_id',
        'seller_listing_id',
        'product_id',
        'product_variant_id',
        'seller_sku',
        'unit_of_measurement',
        'is_unit_compatible',
        'old_seller_price',
        'new_seller_price',
        'old_selling_price',
        'new_selling_price',
        'old_mrp',
        'new_mrp',
        'rounding_difference',
        'status',
        'error_message',
    ];

    protected $casts = [
        'is_unit_compatible' => 'boolean',
        'old_seller_price' => 'float',
        'new_seller_price' => 'float',
        'old_selling_price' => 'float',
        'new_selling_price' => 'float',
        'old_mrp' => 'float',
        'new_mrp' => 'float',
        'rounding_difference' => 'float',
    ];

    public function revision(): BelongsTo
    {
        return $this->belongsTo(PriceRevision::class, 'price_revision_id');
    }

    public function sellerListing(): BelongsTo
    {
        return $this->belongsTo(SellerListing::class, 'seller_listing_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}

