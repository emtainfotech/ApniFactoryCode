<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class SellerFulfilment extends Model
{
    use HasFactory;

    protected $table = 'seller_fulfilments';

    public const STATUS_PENDING_ACCEPTANCE = 'pending_acceptance';
    public const STATUS_ACCEPTED           = 'accepted';
    public const STATUS_PROCESSING_PACKED  = 'processing_packed';
    public const STATUS_DISPATCHED         = 'dispatched';
    public const STATUS_BUYER_ACKNOWLEDGED = 'buyer_acknowledged';
    public const STATUS_DELIVERED          = 'delivered';
    public const STATUS_REJECTED           = 'rejected';
    public const STATUS_CANCELLED          = 'cancelled';

    public const DISPUTE_NONE     = 'none';
    public const DISPUTE_SHORTAGE = 'shortage_reported';
    public const DISPUTE_DAMAGE   = 'damage_reported';
    public const DISPUTE_DISPUTED = 'disputed';

    public const DEFAULT_FREIGHT_TERMS = 'To-Pay (Buyer-Paid Freight)';
    public const FREIGHT_TERMS_TO_PAY   = 'To-Pay (Buyer-Paid Freight)';

    protected $fillable = [
        'fulfilment_ref',
        'parent_order_id',
        'order_id',
        'seller_user_id',
        'customer_id',
        'company_id',
        'dispatch_origin_type',
        'dispatch_origin_name',
        'dispatch_origin_address',
        'dispatch_origin_city',
        'dispatch_origin_state',
        'dispatch_origin_pincode',
        'dispatch_origin_contact',
        'handover_type',
        'handover_location_name',
        'handover_address',
        'handover_city',
        'handover_state',
        'handover_pincode',
        'handover_instructions',
        'transporter_name',
        'transporter_contact',
        'transporter_tracking_no',
        'lr_number',
        'lr_document_path',
        'lr_document_name',
        'lr_file_size',
        'lr_mime_type',
        'lr_uploaded_at',
        'lr_uploaded_by_user_id',
        'dispatched_at',
        'package_count',
        'weight_volume',
        'vehicle_number',
        'estimated_delivery_date',
        'freight_terms',
        'freight_notes',
        'fulfilment_status',
        'buyer_acknowledged_at',
        'buyer_acknowledgement_notes',
        'dispute_status',
        'dispute_reason',
        'dispute_reported_at',
        'notes',
    ];

    protected $casts = [
        'dispatched_at'           => 'datetime',
        'lr_uploaded_at'          => 'datetime',
        'buyer_acknowledged_at'   => 'datetime',
        'dispute_reported_at'     => 'datetime',
        'estimated_delivery_date' => 'date',
        'package_count'           => 'integer',
        'lr_file_size'            => 'integer',
        'parent_order_id'         => 'integer',
        'order_id'                => 'integer',
        'seller_user_id'          => 'integer',
        'customer_id'             => 'integer',
        'company_id'              => 'integer',
    ];

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(SellerFulfilmentHistory::class, 'seller_fulfilment_id')->orderBy('id', 'desc');
    }

    public function isAccepted(): bool
    {
        return in_array($this->fulfilment_status, [
            self::STATUS_ACCEPTED,
            self::STATUS_PROCESSING_PACKED,
            self::STATUS_DISPATCHED,
            self::STATUS_BUYER_ACKNOWLEDGED,
            self::STATUS_DELIVERED,
        ]);
    }

    public function isDispatched(): bool
    {
        return in_array($this->fulfilment_status, [
            self::STATUS_DISPATCHED,
            self::STATUS_BUYER_ACKNOWLEDGED,
            self::STATUS_DELIVERED,
        ]);
    }

    public function isAcknowledged(): bool
    {
        return in_array($this->fulfilment_status, [
            self::STATUS_BUYER_ACKNOWLEDGED,
            self::STATUS_DELIVERED,
        ]);
    }

    public function hasLrDocument(): bool
    {
        return !empty($this->lr_document_path) || !empty($this->lr_number);
    }

    /**
     * Validate whether transitioning from the current status to a target status is allowed.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        $allowed = [
            self::STATUS_PENDING_ACCEPTANCE => [self::STATUS_ACCEPTED, self::STATUS_REJECTED],
            self::STATUS_ACCEPTED           => [self::STATUS_PROCESSING_PACKED, self::STATUS_DISPATCHED, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING_PACKED  => [self::STATUS_DISPATCHED, self::STATUS_CANCELLED],
            self::STATUS_DISPATCHED         => [self::STATUS_BUYER_ACKNOWLEDGED, self::STATUS_DELIVERED],
            self::STATUS_BUYER_ACKNOWLEDGED => [self::STATUS_DELIVERED],
            self::STATUS_DELIVERED          => [],
            self::STATUS_REJECTED           => [],
            self::STATUS_CANCELLED          => [],
        ];

        return in_array($targetStatus, $allowed[$this->fulfilment_status] ?? []);
    }
}
