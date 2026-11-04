<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
     protected $fillable = [
        'title',
        'name',
        'image',
        'status',
        'maincategory_id',
        'addby',
        'adminmsg',
        'adminstatus','sequence'
    ];
    
    public function maincategory(){
        return $this->belongsTo(MainCategory::class);
    }

    public function subcategories(){
        return $this->hasMany(SubCatgory::class, 'cid');
    }

    public function categoryAttributes(){
        return $this->hasMany(CategoryAttribute::class, 'category_id');
    }

    public function attributes(){
        return $this->belongsToMany(Attribute::class, 'category_attributes', 'category_id', 'attribute_id')
            ->withPivot(['is_required', 'is_filterable', 'is_searchable', 'is_variant_generating', 'sort_order', 'validation_rules', 'status']);
    }

    public function getStatusAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return in_array(strtolower($value ?? ''), ['active', '1']) ? 1 : 0;
        }
        return $value;
    }
  
}
