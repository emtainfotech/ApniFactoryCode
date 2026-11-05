<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCommissionSnapshot extends Model
{
    use HasFactory;

    protected $table = 'order_commission_snapshots';

    protected $fillable = [
        'order_id',
        'order_detail_id',
        'orderno',
        'seller_user_id',
        'company_id',
        'seller_listing_id',
        'product_id',
        'product_variant_id',
        'commission_rule_id',
        'commission_rule_code',
        'rule_scope_type',
        'rule_scope_id',
        'commission_percentage',
        'fixed_fee',
        'calculation_basis',
        'quantity',
        'unit_price',
        'base_amount',
        'commission_amount',
        'gst_rate',
        'gst_amount',
        'total_platform_fee',
        'seller_payable',
        'tax_treatment',
        'snapshot_timestamp',
    ];

    protected $casts = [
        'commission_percentage' => 'float',
        'fixed_fee'             => 'float',
        'quantity'              => 'integer',
        'unit_price'            => 'float',
        'base_amount'           => 'float',
        'commission_amount'     => 'float',
        'gst_rate'              => 'float',
        'gst_amount'            => 'float',
        'total_platform_fee'    => 'float',
        'seller_payable'        => 'float',
        'tax_treatment'         => 'array',
        'snapshot_timestamp'    => 'datetime',
        'order_id'              => 'integer',
        'order_detail_id'       => 'integer',
        'seller_user_id'        => 'integer',
        'company_id'            => 'integer',
        'seller_listing_id'     => 'integer',
        'product_id'            => 'integer',
        'product_variant_id'    => 'integer',
        'commission_rule_id'    => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(SellerListing::class, 'seller_listing_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
