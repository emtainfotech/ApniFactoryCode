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
        'approval_status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'correction_notes',
        'last_validated_at',
        'brand_id',
        'shadecard',
        'user_id',
        'hsncode',
        'tax',
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

    /**
     * Phase-II Hierarchy & Dynamic Attributes
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function listings()
    {
        return $this->hasMany(SellerListing::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function validationResults()
    {
        return $this->hasMany(ProductValidationResult::class)->orderBy('id', 'desc');
    }

    public function latestValidationResult()
    {
        return $this->hasOne(ProductValidationResult::class)->latestOfMany();
    }

    public function reviewHistories()
    {
        return $this->hasMany(ProductReviewHistory::class)->orderBy('id', 'desc');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPendingReview(): bool
    {
        return in_array($this->approval_status, ['pending_review', 'resubmitted'], true);
    }

    public function canBeSubmitted(): bool
    {
        return in_array($this->approval_status, ['draft', 'rejected', 'corrections_requested'], true);
    }

    public function scopeMarketplaceVisible($query)
    {
        return $query->whereIn('status', ['Active', '1'])
                     ->where('approval_status', 'approved');
    }

    public function scopePendingReview($query)
    {
        return $query->whereIn('approval_status', ['pending_review', 'resubmitted']);
    }

    protected $casts = [
        'shadecard'         => 'array',
        'submitted_at'      => 'datetime',
        'reviewed_at'       => 'datetime',
        'last_validated_at' => 'datetime',
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
