<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReviewHistory extends Model
{
    use HasFactory;

    protected $table = 'product_review_histories';

    protected $fillable = [
        'product_id',
        'company_id',
        'seller_user_id',
        'reviewer_id',
        'action',
        'previous_status',
        'new_status',
        'notes',
        'validation_result_id',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function validationResult()
    {
        return $this->belongsTo(ProductValidationResult::class, 'validation_result_id');
    }
}

