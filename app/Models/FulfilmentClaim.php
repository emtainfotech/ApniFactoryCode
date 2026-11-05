<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FulfilmentClaim extends Model
{
    use HasFactory;

    protected $table = 'fulfilment_claims';

    public const TYPE_SHORTAGE   = 'shortage';
    public const TYPE_DAMAGE     = 'damage';
    public const TYPE_WRONG_ITEM = 'wrong_item';
    public const TYPE_OTHER      = 'other';

    public const STATUS_SUBMITTED        = 'submitted';
    public const STATUS_SELLER_RESPONDED = 'seller_responded';
    public const STATUS_UNDER_REVIEW     = 'under_review';
    public const STATUS_RESOLVED         = 'resolved_settled';
    public const STATUS_REJECTED         = 'rejected';
    public const STATUS_CANCELLED        = 'cancelled';

    protected $fillable = [
        'claim_ref',
        'seller_fulfilment_id',
        'parent_order_id',
        'order_id',
        'seller_user_id',
        'customer_id',
        'claim_type',
        'claim_status',
        'description',
        'seller_response',
        'seller_responded_at',
        'seller_response_by_user_id',
        'admin_notes',
        'resolution_summary',
        'resolved_by_user_id',
        'resolved_at',
        'idempotency_key',
    ];

    protected $casts = [
        'seller_responded_at' => 'datetime',
        'resolved_at'         => 'datetime',
    ];

    public function sellerFulfilment(): BelongsTo
    {
        return $this->belongsTo(SellerFulfilment::class, 'seller_fulfilment_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function buyerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FulfilmentClaimItem::class, 'fulfilment_claim_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(FulfilmentClaimEvidence::class, 'fulfilment_claim_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(FulfilmentClaimHistory::class, 'fulfilment_claim_id')->orderBy('id', 'asc');
    }

    public function isOpen(): bool
    {
        return !in_array($this->claim_status, [
            self::STATUS_RESOLVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ], true);
    }

    /**
     * Allowed state transitions for claim workflow.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        $allowed = [
            self::STATUS_SUBMITTED => [
                self::STATUS_SELLER_RESPONDED,
                self::STATUS_UNDER_REVIEW,
                self::STATUS_RESOLVED,
                self::STATUS_REJECTED,
                self::STATUS_CANCELLED,
            ],
            self::STATUS_SELLER_RESPONDED => [
                self::STATUS_UNDER_REVIEW,
                self::STATUS_RESOLVED,
                self::STATUS_REJECTED,
            ],
            self::STATUS_UNDER_REVIEW => [
                self::STATUS_RESOLVED,
                self::STATUS_REJECTED,
                self::STATUS_CANCELLED,
            ],
            self::STATUS_RESOLVED  => [], // terminal
            self::STATUS_REJECTED  => [], // terminal
            self::STATUS_CANCELLED => [], // terminal
        ];

        return in_array($targetStatus, $allowed[$this->claim_status] ?? [], true);
    }
}
