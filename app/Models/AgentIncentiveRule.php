<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentIncentiveRule extends Model
{
    use HasFactory;

    protected $table = 'agent_incentive_rules';

    public const FUNDING_APNI_FACTORY = 'apnifactory_funded';

    public const METHOD_PERCENTAGE_ORDER      = 'percentage_of_order_value';
    public const METHOD_PERCENTAGE_COMMISSION = 'percentage_of_platform_commission';
    public const METHOD_FIXED_AMOUNT          = 'fixed_amount';

    public const BASE_TAXABLE_ORDER      = 'taxable_order_value';
    public const BASE_GROSS_ORDER        = 'gross_order_value';
    public const BASE_COMMISSION_EX_GST  = 'platform_commission_ex_gst';
    public const BASE_COMMISSION_INCL_GST = 'platform_commission_incl_gst';
    public const BASE_FIXED_PER_ORDER    = 'fixed_per_order';

    public const ROUND_HALF_UP_2     = 'half_up_2';
    public const ROUND_FLOOR_0       = 'floor_0';
    public const ROUND_CEIL_0        = 'ceil_0';
    public const ROUND_NEAREST_RUPEE = 'nearest_rupee';

    public const TAX_PENDING_DECISION = 'pending_tax_decision';
    public const TAX_EXCLUSIVE_NO_GST = 'exclusive_no_gst';
    public const TAX_TDS_CONFIGURABLE = 'tds_applicable_configurable';
    public const TAX_GST_INCLUSIVE    = 'gst_inclusive';

    public const STATUS_DRAFT            = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED         = 'approved';
    public const STATUS_ACTIVE           = 'active';
    public const STATUS_INACTIVE         = 'inactive';
    public const STATUS_SUPERSEDED       = 'superseded';
    public const STATUS_REJECTED         = 'rejected';

    protected $fillable = [
        'rule_code',
        'version',
        'parent_rule_id',
        'name',
        'funding_source',
        'calculation_method',
        'calculation_base',
        'rate_percentage',
        'fixed_amount',
        'max_cap_amount',
        'rounding_rule',
        'tax_treatment_code',
        'tds_percentage',
        'tax_notes',
        'effective_from',
        'effective_to',
        'status',
        'agreement_ref',
        'reason',
        'created_by_user_id',
        'updated_by_user_id',
        'business_approved_by_user_id',
        'business_approved_at',
        'business_approval_ref',
        'finance_approved_by_user_id',
        'finance_approved_at',
        'finance_approval_ref',
        'approved_by_user_id',
        'approved_at',
        'deactivated_by_user_id',
        'deactivated_at',
        'deactivation_reason',
    ];

    protected $casts = [
        'version'                      => 'integer',
        'parent_rule_id'               => 'integer',
        'rate_percentage'              => 'float',
        'fixed_amount'                 => 'float',
        'max_cap_amount'               => 'float',
        'tds_percentage'               => 'float',
        'effective_from'               => 'datetime',
        'effective_to'                 => 'datetime',
        'business_approved_at'         => 'datetime',
        'finance_approved_at'          => 'datetime',
        'approved_at'                  => 'datetime',
        'deactivated_at'               => 'datetime',
        'created_by_user_id'           => 'integer',
        'updated_by_user_id'           => 'integer',
        'business_approved_by_user_id' => 'integer',
        'finance_approved_by_user_id'  => 'integer',
        'approved_by_user_id'          => 'integer',
        'deactivated_by_user_id'       => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $rule) {
            $lockedStatuses = [self::STATUS_APPROVED, self::STATUS_ACTIVE, self::STATUS_SUPERSEDED];
            $originalStatus = $rule->getOriginal('status');

            if (in_array($originalStatus, $lockedStatuses, true)) {
                $immutableFormulaFields = [
                    'rule_code',
                    'version',
                    'calculation_method',
                    'calculation_base',
                    'rate_percentage',
                    'fixed_amount',
                    'max_cap_amount',
                    'rounding_rule',
                    'tax_treatment_code',
                    'tds_percentage',
                    'funding_source',
                ];

                foreach ($immutableFormulaFields as $field) {
                    if ($rule->isDirty($field)) {
                        throw new \LogicException("Cannot mutate commercial parameter '{$field}' on {$originalStatus} rule #{$rule->rule_code} v{$rule->version}. Create a new rule version instead.");
                    }
                }
            }
        });

        static::deleting(function (self $rule) {
            throw new \LogicException('Incentive rules cannot be deleted for audit and historical reproducibility. Deactivate or supersede instead.');
        });
    }

    public function parentRule(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_rule_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function businessApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_approved_by_user_id');
    }

    public function financeApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_approved_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(AgentIncentiveLedger::class, 'agent_incentive_rule_id');
    }

    public function scopeActiveEffective($query, $at = null)
    {
        $ts = $at ? Carbon::parse($at) : now();

        return $query->where('status', self::STATUS_ACTIVE)
            ->where('effective_from', '<=', $ts)
            ->where(function ($q) use ($ts) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $ts);
            });
    }

    public function hasBusinessApproval(): bool
    {
        return !empty($this->business_approved_at) && !empty($this->business_approved_by_user_id);
    }

    public function hasFinanceApproval(): bool
    {
        return !empty($this->finance_approved_at) && !empty($this->finance_approved_by_user_id);
    }

    public function hasRequiredApprovals(): bool
    {
        return $this->hasBusinessApproval() && $this->hasFinanceApproval();
    }

    public function isEffectiveAt($at = null): bool
    {
        $ts = $at ? Carbon::parse($at) : now();
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        if ($this->effective_from && $this->effective_from->gt($ts)) {
            return false;
        }
        if ($this->effective_to && $this->effective_to->lt($ts)) {
            return false;
        }

        return true;
    }

    public static function validBasesForMethod(string $method): array
    {
        return match ($method) {
            self::METHOD_PERCENTAGE_ORDER      => [self::BASE_TAXABLE_ORDER, self::BASE_GROSS_ORDER],
            self::METHOD_PERCENTAGE_COMMISSION => [self::BASE_COMMISSION_EX_GST, self::BASE_COMMISSION_INCL_GST],
            self::METHOD_FIXED_AMOUNT          => [self::BASE_FIXED_PER_ORDER],
            default                            => [],
        };
    }
}

