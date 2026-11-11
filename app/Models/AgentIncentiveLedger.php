<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentIncentiveLedger extends Model
{
    use HasFactory;

    protected $table = 'agent_incentive_ledgers';

    public const TYPE_EARNED     = 'earned';
    public const TYPE_REVERSAL   = 'reversal';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const STATUS_PENDING_REVIEW               = 'pending_review';
    public const STATUS_CALCULATED_PENDING_APPROVAL  = 'calculated_pending_approval';
    public const STATUS_ON_HOLD                      = 'on_hold';
    public const STATUS_APPROVED                     = 'approved';
    public const STATUS_REJECTED                     = 'rejected';
    public const STATUS_PARTIALLY_REVERSED           = 'partially_reversed';
    public const STATUS_REVERSED                     = 'reversed';
    public const STATUS_CANCELLED                    = 'cancelled';
    public const STATUS_PAID                         = 'paid';

    public const EXPENSE_APNI_FACTORY_PLATFORM = 'apnifactory_platform_expense';

    protected $fillable = [
        'ledger_ref',
        'entry_type',
        'earned_parent_order_lock',
        'customer_id',
        'field_agent_id',
        'buyer_agent_attribution_id',
        'agent_eligible_order_id',
        'parent_order_id',
        'seller_fulfilment_refs',
        'eligible_order_sequence',
        'eligibility_event_ref',
        'eligibility_event_at',
        'agent_incentive_rule_id',
        'rule_code',
        'rule_version',
        'calculation_method',
        'calculation_base',
        'calculation_base_amount',
        'applied_rate',
        'applied_fixed_amount',
        'max_cap_amount',
        'cap_applied',
        'rounding_rule',
        'tax_treatment_snapshot',
        'gross_incentive_amount',
        'tax_deduction_amount',
        'calculated_incentive_amount',
        'funding_expense_account',
        'funding_policy_approved_snapshot',
        'status',
        'reference_ledger_id',
        'reversal_or_adjustment_ref',
        'payout_reference',
        'agent_payout_id',
        'idempotency_key',
        'reason',
        'admin_notes',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'hold_reason',
        'rejected_by_user_id',
        'rejected_at',
        'rejection_reason',
    ];

    protected $hidden = [
        'admin_notes',
    ];

    protected $casts = [
        'seller_fulfilment_refs'           => 'array',
        'tax_treatment_snapshot'           => 'array',
        'eligible_order_sequence'          => 'integer',
        'rule_version'                     => 'integer',
        'calculation_base_amount'          => 'float',
        'applied_rate'                     => 'float',
        'applied_fixed_amount'             => 'float',
        'max_cap_amount'                   => 'float',
        'cap_applied'                      => 'boolean',
        'gross_incentive_amount'           => 'float',
        'tax_deduction_amount'             => 'float',
        'calculated_incentive_amount'      => 'float',
        'funding_policy_approved_snapshot' => 'boolean',
        'eligibility_event_at'             => 'datetime',
        'approved_at'                      => 'datetime',
        'reviewed_at'                      => 'datetime',
        'rejected_at'                      => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $entry) {
            $immutableColumns = [
                'ledger_ref',
                'entry_type',
                'customer_id',
                'field_agent_id',
                'buyer_agent_attribution_id',
                'agent_eligible_order_id',
                'parent_order_id',
                'eligible_order_sequence',
                'eligibility_event_ref',
                'agent_incentive_rule_id',
                'rule_code',
                'rule_version',
                'calculation_method',
                'calculation_base',
                'calculation_base_amount',
                'applied_rate',
                'applied_fixed_amount',
                'max_cap_amount',
                'cap_applied',
                'rounding_rule',
                'gross_incentive_amount',
                'tax_deduction_amount',
                'calculated_incentive_amount',
                'funding_expense_account',
                'funding_policy_approved_snapshot',
            ];

            foreach ($immutableColumns as $col) {
                if ($entry->isDirty($col)) {
                    throw new \LogicException("Incentive ledger entries are financially immutable ('{$col}' cannot be altered). Post a reversal or adjustment entry referencing ledger #{$entry->ledger_ref}.");
                }
            }
        });

        static::deleting(function () {
            throw new \LogicException('Incentive ledger entries are permanently immutable and cannot be deleted.');
        });
    }

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

    public function eligibleOrder(): BelongsTo
    {
        return $this->belongsTo(AgentEligibleOrder::class, 'agent_eligible_order_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AgentIncentiveRule::class, 'agent_incentive_rule_id');
    }

    public function referenceLedger(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reference_ledger_id');
    }

    public function offsettingEntries(): HasMany
    {
        return $this->hasMany(self::class, 'reference_ledger_id')->orderBy('id', 'asc');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(AgentPayout::class, 'agent_payout_id');
    }

    public function payoutItems(): HasMany
    {
        return $this->hasMany(AgentPayoutItem::class, 'agent_incentive_ledger_id');
    }
}

