<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaintPriceAdjustment extends Model
{
    use HasFactory;

    protected $table = 'paint_price_adjustments';

    protected $fillable = [
        'user_id',
        'product_id',
        'adjustment_type',
        'adjustment_value',
        'scope_type',
        'scope_json',
        'affected_count',
        'preview_data',
        'created_by',
    ];

    protected $casts = [
        'scope_json'   => 'array',
        'preview_data' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
