<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'attributes';

    protected $fillable = [
        'name',
        'code',
        'type',
        'unit',
        'description',
        'sort_order',
        'validation_rules',
        'status',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'sort_order' => 'integer',
    ];

    public function options()
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id')->orderBy('sort_order', 'asc');
    }

    public function categoryMappings()
    {
        return $this->hasMany(CategoryAttribute::class, 'attribute_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attributes', 'attribute_id', 'category_id')
                    ->withPivot(['is_required', 'is_filterable', 'is_searchable', 'is_variant_generating', 'sort_order', 'status']);
    }

    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }
}

