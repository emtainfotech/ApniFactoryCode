<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyerAgentAttribution extends Model
{
    use HasFactory;

    protected $table = 'buyer_agent_attributions';

    public const STATUS_PENDING    = 'pending';
    public const STATUS_ACTIVE     = 'active';
    public const STATUS_REJECTED   = 'rejected';
    public const STATUS_SUPERSEDED = 'superseded';
    public const STATUS_REVOKED    = 'revoked';

    public const APPROVAL_PENDING  = 'pending_approval';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    public const SOURCE_ONBOARDING    = 'onboarding';
    public const SOURCE_ACTIVATION    = 'activation';
    public const SOURCE_ADMIN_LINKING = 'admin_linking';
    public const SOURCE_REASSIGNMENT  = 'reassignment';

    protected $fillable = [
        'attribution_ref',
        'customer_id',
        'field_agent_id',
        'active_customer_lock',
        'status',
        'approval_status',
        'attribution_source',
        'evidence_reference',
        'remarks',
        'admin_notes',
        'attributed_by_user_id',
        'attributed_at',
        'approved_by_user_id',
        'approved_at',
        'rejected_by_user_id',
        'rejected_at',
        'rejection_reason',
        'superseded_by_attribution_id',
        'reassigned_from_attribution_id',
        'reassignment_reason',
        'is_exception_reassignment',
        'exception_approval_ref',
        'eligible_order_limit',
        'idempotency_key',
    ];

    protected $hidden = [
        'admin_notes',
    ];

    protected $casts = [
        'attributed_at'             => 'datetime',
        'approved_at'               => 'datetime',
        'rejected_at'               => 'datetime',
        'is_exception_reassignment' => 'boolean',
        'eligible_order_limit'      => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $model) {
            // Preserve historical agent/buyer linkage: reassignment creates a new record instead of overwriting.
            if ($model->isDirty('customer_id') || $model->isDirty('field_agent_id')) {
                throw new \LogicException('Cannot overwrite buyer or field agent on an existing attribution record. Use reassignment workflow to preserve history.');
            }
        });

        static::deleting(function () {
            throw new \LogicException('Attribution records must be preserved for auditability and cannot be deleted.');
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

    public function attributedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attributed_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_attribution_id');
    }

    public function reassignedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reassigned_from_attribution_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(BuyerAttributionHistory::class, 'buyer_agent_attribution_id')->orderBy('id', 'asc');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->approval_status === self::APPROVAL_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING || $this->approval_status === self::APPROVAL_PENDING;
    }
}

