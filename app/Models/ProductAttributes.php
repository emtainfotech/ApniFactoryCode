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
        'price'
    ];
     public function product(){
        return $this->belongsTo(Product::class);
    }
}
