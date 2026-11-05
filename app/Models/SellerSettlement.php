<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerSettlement extends Model
{
    use HasFactory;

    protected $table = 'seller_settlements';

    public const STATUS_PENDING    = 'pending';
    public const STATUS_ON_HOLD    = 'on_hold';
    public const STATUS_ELIGIBLE   = 'eligible';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'settlement_ref',
        'parent_order_id',
        'order_id',
        'seller_user_id',
        'company_id',
        'gross_order_amount',
        'commission_amount',
        'commission_gst_amount',
        'total_platform_deduction',
        'net_seller_payable',
        'settlement_status',
        'hold_until',
        'payout_reference',
        'processed_at',
        'processed_by_user_id',
        'notes',
    ];

    protected $casts = [
        'gross_order_amount'       => 'decimal:2',
        'commission_amount'        => 'decimal:2',
        'commission_gst_amount'    => 'decimal:2',
        'total_platform_deduction' => 'decimal:2',
        'net_seller_payable'       => 'decimal:2',
        'hold_until'               => 'datetime',
        'processed_at'             => 'datetime',
        'parent_order_id'          => 'integer',
        'order_id'                 => 'integer',
        'seller_user_id'           => 'integer',
        'company_id'               => 'integer',
        'processed_by_user_id'     => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedger::class, 'settlement_id');
    }

    public function isCompleted(): bool
    {
        return $this->settlement_status === self::STATUS_COMPLETED;
    }

    public function isEligible(): bool
    {
        return $this->settlement_status === self::STATUS_ELIGIBLE;
    }

    public function isOnHold(): bool
    {
        return $this->settlement_status === self::STATUS_ON_HOLD;
    }
}

