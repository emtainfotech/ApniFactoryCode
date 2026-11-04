<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    use HasFactory;

    protected $table = 'attribute_options';

    protected $fillable = [
        'attribute_id',
        'label',
        'value',
        'color_hex',
        'sort_order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
