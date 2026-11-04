<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'variant_sku',
        'shade_card_id',
        'box_packing_id',
        'pack_litres',
        'variant_name',
        'image',
        'barcode',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function shadeCard()
    {
        return $this->belongsTo(ShadeCard::class, 'shade_card_id');
    }

    public function boxPacking()
    {
        return $this->belongsTo(BoxPacking::class, 'box_packing_id');
    }

    public function listings()
    {
        return $this->hasMany(SellerListing::class, 'product_variant_id');
    }

    /**
     * Best (lowest priced) active listing for Buy-Box presentation
     */
    public function primaryListing()
    {
        return $this->hasOne(SellerListing::class, 'product_variant_id')
                    ->where('status', 'Active')
                    ->where('stock_quantity', '>', 0)
                    ->orderBy('selling_price', 'asc');
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_variant_id');
    }
}

