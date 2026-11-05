<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialLedger extends Model
{
    use HasFactory;

    protected $table = 'financial_ledgers';

    public const DIRECTION_CREDIT = 'credit';
    public const DIRECTION_DEBIT  = 'debit';

    public const TYPE_AMOUNT_COLLECTED        = 'amount_collected';
    public const TYPE_SELLER_PAYABLE          = 'seller_payable';
    public const TYPE_PLATFORM_COMMISSION     = 'platform_commission';
    public const TYPE_GST_ON_COMMISSION       = 'gst_on_commission';
    public const TYPE_GATEWAY_FEE             = 'gateway_fee';
    public const TYPE_REFUND                  = 'refund';
    public const TYPE_CREDIT_NOTE_ADJUSTMENT  = 'credit_note_adjustment';
    public const TYPE_PAYOUT_RELEASED         = 'payout_released';

    protected $fillable = [
        'entry_ref',
        'seller_user_id',
        'company_id',
        'parent_order_id',
        'order_id',
        'transection_id',
        'settlement_id',
        'entry_type',
        'direction',
        'amount',
        'currency',
        'running_balance',
        'source_process',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'running_balance'    => 'decimal:2',
        'seller_user_id'     => 'integer',
        'company_id'         => 'integer',
        'parent_order_id'    => 'integer',
        'order_id'           => 'integer',
        'transection_id'     => 'integer',
        'settlement_id'      => 'integer',
        'created_by_user_id' => 'integer',
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

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(SellerSettlement::class, 'settlement_id');
    }
}

