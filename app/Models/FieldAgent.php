<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldAgent extends Model
{
    use HasFactory;

    protected $table = 'field_agents';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const KYC_NOT_SUBMITTED  = 'not_submitted';
    public const KYC_PENDING_REVIEW = 'pending_review';
    public const KYC_VERIFIED       = 'verified';
    public const KYC_REJECTED       = 'rejected';
    public const KYC_EXPIRED        = 'expired';

    protected $fillable = [
        'user_id',
        'agent_code',
        'name',
        'email',
        'mobile',
        'alternate_mobile',
        'territory',
        'territory_city',
        'territory_state',
        'status',
        'joining_date',
        'supervisor_user_id',
        'supervisor_name',
        'payout_details',
        'kyc_status',
        'contract_status',
        'kyc_reference',
        'notes',
        'deactivated_at',
        'deactivation_reason',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $hidden = [
        'payout_details',
        'kyc_reference',
        'notes',
    ];

    protected $casts = [
        'joining_date'   => 'date',
        'deactivated_at' => 'datetime',
        'payout_details' => 'encrypted:array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(BuyerAgentAttribution::class, 'field_agent_id');
    }

    public function activeAttributions(): HasMany
    {
        return $this->hasMany(BuyerAgentAttribution::class, 'field_agent_id')
            ->where('status', BuyerAgentAttribution::STATUS_ACTIVE);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(FieldAgentStatusHistory::class, 'field_agent_id')->orderBy('id', 'desc');
    }

    /**
     * Only active agents may receive new buyer attributions.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Build a masked summary of sensitive payout metadata without exposing raw account/UPI secrets.
     */
    public function maskedPayoutSummary(): array
    {
        $details = $this->payout_details;
        if (empty($details) || !is_array($details)) {
            return ['configured' => false];
        }

        $maskedAccount = null;
        if (!empty($details['account_number'])) {
            $raw = (string)$details['account_number'];
            $maskedAccount = strlen($raw) > 4
                ? str_repeat('*', max(4, strlen($raw) - 4)) . substr($raw, -4)
                : '****';
        }

        $maskedUpi = null;
        if (!empty($details['upi_id'])) {
            $upi = (string)$details['upi_id'];
            $parts = explode('@', $upi, 2);
            $prefix = strlen($parts[0]) > 2 ? substr($parts[0], 0, 2) . '***' : '***';
            $maskedUpi = $prefix . (isset($parts[1]) ? '@' . $parts[1] : '');
        }

        return [
            'configured'          => true,
            'account_holder_name' => $details['account_holder_name'] ?? null,
            'bank_name'           => $details['bank_name'] ?? null,
            'ifsc_code'           => $details['ifsc_code'] ?? null,
            'account_masked'      => $maskedAccount,
            'upi_masked'          => $maskedUpi,
        ];
    }

    /**
     * Sanitized profile view for the logged-in field agent (excludes confidential admin notes & raw payout secrets).
     */
    public function toAgentSelfArray(): array
    {
        return [
            'id'                    => $this->id,
            'agent_code'            => $this->agent_code,
            'name'                  => $this->name,
            'email'                 => $this->email,
            'mobile'                => $this->mobile,
            'alternate_mobile'      => $this->alternate_mobile,
            'territory'             => $this->territory,
            'territory_city'        => $this->territory_city,
            'territory_state'       => $this->territory_state,
            'status'                => $this->status,
            'joining_date'          => $this->joining_date ? $this->joining_date->toDateString() : null,
            'supervisor_name'       => $this->supervisor_name ?? $this->supervisor?->name,
            'kyc_status'            => $this->kyc_status,
            'contract_status'       => $this->contract_status,
            'payout_summary'        => $this->maskedPayoutSummary(),
        ];
    }
}

