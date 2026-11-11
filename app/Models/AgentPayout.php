<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentPayout extends Model
{
    use HasFactory;

    protected $table = 'agent_payouts';

    public const STATUS_DRAFT_AWAITING_POLICY    = 'draft_awaiting_policy';
    public const STATUS_PENDING_AUTHORIZATION    = 'pending_authorization';
    public const STATUS_AUTHORIZED               = 'authorized';
    public const STATUS_PROCESSING_INITIATED     = 'processing_initiated';
    public const STATUS_PAID_CONFIRMED           = 'paid_confirmed';
    public const STATUS_FAILED                   = 'failed';
    public const STATUS_REJECTED                 = 'rejected';
    public const STATUS_CANCELLED                = 'cancelled';
    public const STATUS_RECONCILIATION_EXCEPTION = 'reconciliation_exception';

    public const METHOD_AWAITING_POLICY     = 'unconfigured_awaiting_policy';
    public const METHOD_MANUAL_BANK         = 'manual_bank_transfer';
    public const METHOD_MANUAL_UPI          = 'manual_upi';
    public const METHOD_APPROVED_BANK_API   = 'approved_bank_api';

    public const RECON_UNRECONCILED         = 'unreconciled';
    public const RECON_MATCHED              = 'matched';
    public const RECON_MISMATCH             = 'exception_mismatch';
    public const RECON_DUPLICATE_DETECTED   = 'duplicate_detected';
    public const RECON_UNMATCHED_EXTERNAL   = 'unmatched_external_ref';
    public const RECON_REVERSED             = 'reversed';

    protected $fillable = [
        'payout_ref',
        'field_agent_id',
        'currency',
        'gross_incentive_amount',
        'approved_adjustments_amount',
        'withholding_tax_rate',
        'withholding_tax_amount',
        'net_payable_amount',
        'funding_expense_account',
        'payment_method',
        'status',
        'payout_destination_snapshot',
        'external_transaction_ref',
        'external_provider_name',
        'payment_confirmation_source',
        'reconciliation_status',
        'reconciled_at',
        'reconciled_by_user_id',
        'reconciliation_notes',
        'idempotency_key',
        'callback_event_id',
        'initiated_by_user_id',
        'initiated_at',
        'authorized_by_user_id',
        'authorized_at',
        'paid_at',
        'payment_date',
        'failure_reason',
        'rejection_reason',
        'admin_notes',
    ];

    protected $hidden = [
        'payout_destination_snapshot',
        'admin_notes',
    ];

    protected $casts = [
        'payout_destination_snapshot' => 'encrypted:array',
        'gross_incentive_amount'      => 'float',
        'approved_adjustments_amount' => 'float',
        'withholding_tax_rate'        => 'float',
        'withholding_tax_amount'      => 'float',
        'net_payable_amount'          => 'float',
        'reconciled_at'               => 'datetime',
        'initiated_at'                => 'datetime',
        'authorized_at'               => 'datetime',
        'paid_at'                     => 'datetime',
        'payment_date'                => 'date',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $payout) {
            $locked = [
                self::STATUS_AUTHORIZED,
                self::STATUS_PROCESSING_INITIATED,
                self::STATUS_PAID_CONFIRMED,
            ];
            $originalStatus = $payout->getOriginal('status');

            if (in_array($originalStatus, $locked, true)) {
                $immutableFields = [
                    'payout_ref',
                    'field_agent_id',
                    'gross_incentive_amount',
                    'approved_adjustments_amount',
                    'withholding_tax_amount',
                    'net_payable_amount',
                    'funding_expense_account',
                ];
                foreach ($immutableFields as $col) {
                    if ($payout->isDirty($col)) {
                        throw new \LogicException("Payout '{$payout->payout_ref}' ({$originalStatus}) is financially locked; '{$col}' cannot be altered.");
                    }
                }
            }
        });

        static::deleting(function () {
            throw new \LogicException('Payout records are permanently retained for financial reconciliation and cannot be deleted.');
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FieldAgent::class, 'field_agent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AgentPayoutItem::class, 'agent_payout_id')->orderBy('id', 'asc');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(AgentIncentiveLedger::class, 'agent_payout_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(AgentPayoutReconciliationException::class, 'agent_payout_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by_user_id');
    }

    public function maskedDestinationSummary(): array
    {
        $details = $this->payout_destination_snapshot;
        if (empty($details) || !is_array($details)) {
            return ['configured' => false];
        }

        return [
            'configured'          => (bool)($details['configured'] ?? true),
            'account_holder_name' => $details['account_holder_name'] ?? null,
            'bank_name'           => $details['bank_name'] ?? null,
            'ifsc_code'           => $details['ifsc_code'] ?? null,
            'account_masked'      => $details['account_masked'] ?? null,
            'upi_masked'          => $details['upi_masked'] ?? null,
        ];
    }

    public function toAgentSafeArray(): array
    {
        return [
            'id'                          => $this->id,
            'payout_ref'                  => $this->payout_ref,
            'currency'                    => $this->currency,
            'gross_incentive_amount'      => (float)$this->gross_incentive_amount,
            'approved_adjustments_amount' => (float)$this->approved_adjustments_amount,
            'withholding_tax_amount'      => (float)$this->withholding_tax_amount,
            'net_payable_amount'          => (float)$this->net_payable_amount,
            'payment_method'              => $this->payment_method,
            'status'                      => $this->status,
            'reconciliation_status'       => $this->reconciliation_status,
            'external_transaction_ref'    => $this->status === self::STATUS_PAID_CONFIRMED ? $this->external_transaction_ref : null,
            'payment_date'                => $this->payment_date?->toDateString(),
            'paid_at'                     => $this->paid_at?->toIso8601String(),
            'destination_summary'         => $this->maskedDestinationSummary(),
            'failure_reason'              => $this->status === self::STATUS_FAILED ? $this->failure_reason : null,
            'created_at'                  => $this->created_at?->toIso8601String(),
        ];
    }
}

