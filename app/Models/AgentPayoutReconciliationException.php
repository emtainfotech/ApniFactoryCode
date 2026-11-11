<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentPayoutReconciliationException extends Model
{
    use HasFactory;

    protected $table = 'agent_payout_reconciliation_exceptions';

    public const TYPE_FAILED_PAYOUT            = 'failed_payout';
    public const TYPE_DUPLICATE_PAYMENT        = 'duplicate_payment_detected';
    public const TYPE_UNMATCHED_EXTERNAL_REF   = 'unmatched_external_reference';
    public const TYPE_AMOUNT_MISMATCH          = 'amount_mismatch';
    public const TYPE_POST_PAYOUT_REVERSAL     = 'post_payout_refund_reversal';
    public const TYPE_PRE_PAYOUT_REVERSAL      = 'pre_payout_reversal_impacted';
    public const TYPE_PENDING_FINANCE_REVIEW   = 'pending_finance_review';

    public const STATUS_OPEN         = 'open';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED     = 'resolved';
    public const STATUS_DISMISSED    = 'dismissed';

    protected $fillable = [
        'exception_ref',
        'agent_payout_id',
        'agent_incentive_ledger_id',
        'field_agent_id',
        'exception_type',
        'external_reference',
        'expected_amount',
        'reported_amount',
        'status',
        'description',
        'resolution_notes',
        'created_by_user_id',
        'resolved_by_user_id',
        'resolved_at',
    ];

    protected $casts = [
        'expected_amount' => 'float',
        'reported_amount' => 'float',
        'resolved_at'     => 'datetime',
    ];

    public function payout(): BelongsTo
    {
        return $this->belongsTo(AgentPayout::class, 'agent_payout_id');
    }

    public function ledgerEntry(): BelongsTo
    {
        return $this->belongsTo(AgentIncentiveLedger::class, 'agent_incentive_ledger_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FieldAgent::class, 'field_agent_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}

