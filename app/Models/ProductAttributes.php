<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributes extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'color',
        'quantity',
        'oldprice',
        'seller_price',
        'commission_rate',
        'pack_litres',
        'price',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function shadeCard()
    {
        return $this->belongsTo(ShadeCard::class, 'color');
    }

    public function boxPacking()
    {
        return $this->belongsTo(BoxPacking::class, 'quantity');
    }

    /**
     * Compute customer price dynamically from seller factory price and commission.
     */
    public function calculateCustomerPrice($sellerPrice = null, $commission = null)
    {
        $base = $sellerPrice !== null ? (float)$sellerPrice : (float)($this->seller_price ?: $this->price);
        $rate = $commission !== null ? (float)$commission : (float)($this->commission_rate ?: 25.00);
        return round($base * (1 + ($rate / 100)), 2);
    }
}
