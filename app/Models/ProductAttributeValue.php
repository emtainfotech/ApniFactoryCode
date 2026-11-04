<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $table = 'product_attribute_values';

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'attribute_id',
        'attribute_option_id',
        'text_value',
        'number_value',
        'boolean_value',
        'date_value',
        'json_value',
    ];

    protected $casts = [
        'number_value' => 'decimal:4',
        'boolean_value' => 'boolean',
        'date_value' => 'date',
        'json_value' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function option()
    {
        return $this->belongsTo(AttributeOption::class, 'attribute_option_id');
    }

    /**
     * Resolves the value based on the attribute's type.
     */
    public function getResolvedValueAttribute()
    {
        if ($this->attribute_option_id && $this->option) {
            return $this->option->label ?? $this->option->value;
        }

        if ($this->number_value !== null) {
            return (float) $this->number_value;
        }

        if ($this->boolean_value !== null) {
            return (bool) $this->boolean_value;
        }

        if ($this->date_value !== null) {
            return $this->date_value->toDateString();
        }

        if ($this->json_value !== null) {
            return $this->json_value;
        }

        return $this->text_value;
    }
}
