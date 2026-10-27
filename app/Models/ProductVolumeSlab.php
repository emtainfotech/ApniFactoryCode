<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVolumeSlab extends Model
{
    use HasFactory;

    protected $table = 'product_volume_slabs';

    protected $fillable = [
        'product_id',
        'seller_user_id',
        'min_quantity',
        'max_quantity',
        'discount_type',
        'discount_value',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }
}
