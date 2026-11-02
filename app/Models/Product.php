<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
     protected $fillable = [
         'product_id',
       'maincategory_id',
            'category_id',
            'subcategory_id',
            'name',
            'slug',
            'title',
            'image',
            'capacity',
            'description',
            'multipleimages',
            'status',
            'brand_id',
            'shadecard','user_id','hsncode','tax'
    ];
    
    public function maincategory(){
        return $this->belongsTo(MainCategory::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function subcategory(){
        return $this->belongsTo(SubCatgory::class);
    }
    public function brand(){
        return $this->belongsTo(Brand::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function product_attributes(){
        return $this->hasMany(ProductAttributes::class);
    }

    public function attributes(){
        return $this->hasMany(ProductAttributes::class);
    }
    
      protected $casts = [
        'shadecard' => 'array'
    ];      

    public function getStatusAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return in_array(strtolower($value ?? ''), ['active', '1']) ? 1 : 0;
        }
        return $value;
    }

    public function getTaxAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return (float) ($value ?? 0);
        }
        return $value;
    }      
}
