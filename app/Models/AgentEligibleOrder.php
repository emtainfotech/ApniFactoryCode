<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AgentEligibleOrder extends Model
{
    use HasFactory;

    protected $table = 'agent_eligible_orders';

    public const STATUS_PENDING                  = 'pending_eligibility';
    public const STATUS_PENDING_REVIEW           = 'pending_review';
    public const STATUS_ELIGIBLE                 = 'eligible';
    public const STATUS_INELIGIBLE_UNPAID        = 'ineligible_unpaid';
    public const STATUS_INELIGIBLE_CANCELLED     = 'ineligible_cancelled';
    public const STATUS_INELIGIBLE_REJECTED      = 'ineligible_rejected';
    public const STATUS_INELIGIBLE_REFUNDED      = 'ineligible_refunded';
    public const STATUS_INELIGIBLE_FRAUDULENT    = 'ineligible_fraudulent';
    public const STATUS_INELIGIBLE_LIMIT_EXCEEDED = 'ineligible_limit_exceeded';
    public const STATUS_REVERSED_REFUNDED        = 'reversed_refunded';
    public const STATUS_REVERSED_CANCELLED       = 'reversed_cancelled';

    public const INCENTIVE_NOT_APPLICABLE     = 'not_applicable';
    public const INCENTIVE_PENDING_ELIGIBILITY = 'pending_eligibility';
    public const INCENTIVE_BLOCKED_FUNDING    = 'blocked_unapproved_funding';
    public const INCENTIVE_BLOCKED_NO_RULE    = 'blocked_no_active_rule';
    public const INCENTIVE_PENDING_REVIEW     = 'pending_review';
    public const INCENTIVE_GENERATED          = 'generated';
    public const INCENTIVE_FAILED             = 'failed';

    protected $fillable = [
        'eligibility_ref',
        'customer_id',
        'field_agent_id',
        'buyer_agent_attribution_id',
        'parent_order_id',
        'orderno',
        'seller_fulfilment_refs',
        'seller_count',
        'eligibility_status',
        'sequence_number',
        'active_slot_number',
        'source_event_type',
        'source_event_ref',
        'idempotency_key',
        'payment_verified_at',
        'fulfilled_at',
        'cooling_window_ends_at',
        'eligible_at',
        'incentive_generation_status',
        'incentive_block_reason',
        'evaluation_notes',
        'admin_notes',
    ];

    protected $hidden = [
        'admin_notes',
    ];

    protected $casts = [
        'seller_fulfilment_refs' => 'array',
        'seller_count'           => 'integer',
        'sequence_number'        => 'integer',
        'active_slot_number'     => 'integer',
        'payment_verified_at'    => 'datetime',
        'fulfilled_at'           => 'datetime',
        'cooling_window_ends_at' => 'datetime',
        'eligible_at'            => 'datetime',
        'customer_id'            => 'integer',
        'field_agent_id'         => 'integer',
        'buyer_agent_attribution_id' => 'integer',
        'parent_order_id'        => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FieldAgent::class, 'field_agent_id');
    }

    public function attribution(): BelongsTo
    {
        return $this->belongsTo(BuyerAgentAttribution::class, 'buyer_agent_attribution_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(AgentIncentiveLedger::class, 'agent_eligible_order_id')->orderBy('id', 'asc');
    }

    public function earnedLedgerEntry(): HasOne
    {
        return $this->hasOne(AgentIncentiveLedger::class, 'agent_eligible_order_id')
            ->where('entry_type', AgentIncentiveLedger::TYPE_EARNED);
    }

    public function isSlotOccupying(): bool
    {
        return $this->eligibility_status === self::STATUS_ELIGIBLE && $this->active_slot_number !== null;
    }

    public function toAgentSafeArray(): array
    {
        return [
            'eligibility_ref'    => $this->eligibility_ref,
            'order_no'           => $this->orderno,
            'seller_count'       => (int)$this->seller_count,
            'eligibility_status' => $this->eligibility_status,
            'sequence_number'    => $this->sequence_number,
            'incentive_status'   => $this->incentive_generation_status,
            'eligible_at'        => $this->eligible_at?->toIso8601String(),
            'created_at'         => $this->created_at?->toIso8601String(),
        ];
    }
}

