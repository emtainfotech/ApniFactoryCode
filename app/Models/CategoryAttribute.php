<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAttribute extends Model
{
    use HasFactory;

    protected $table = 'category_attributes';

    protected $fillable = [
        'category_id',
        'attribute_id',
        'is_required',
        'is_filterable',
        'is_searchable',
        'is_variant_generating',
        'sort_order',
        'validation_rules',
        'status',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_searchable' => 'boolean',
        'is_variant_generating' => 'boolean',
        'sort_order' => 'integer',
        'validation_rules' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}

