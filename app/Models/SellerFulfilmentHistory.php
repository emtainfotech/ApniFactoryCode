<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerFulfilmentHistory extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'seller_fulfilment_history';

    protected $fillable = [
        'seller_fulfilment_id',
        'order_id',
        'action',
        'actor_id',
        'actor_name',
        'actor_role',
        'previous_status',
        'new_status',
        'payload',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'payload'              => 'array',
        'created_at'           => 'datetime',
        'seller_fulfilment_id' => 'integer',
        'order_id'             => 'integer',
        'actor_id'             => 'integer',
    ];

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(SellerFulfilment::class, 'seller_fulfilment_id');
    }

    public static function logAction(
        int $fulfilmentId,
        int $orderId,
        string $action,
        ?string $prevStatus = null,
        ?string $newStatus = null,
        ?array $payload = null,
        ?int $actorId = null,
        ?string $actorName = null,
        ?string $actorRole = 'seller',
        ?string $notes = null
    ): self {
        return self::create([
            'seller_fulfilment_id' => $fulfilmentId,
            'order_id'             => $orderId,
            'action'               => $action,
            'previous_status'      => $prevStatus,
            'new_status'           => $newStatus,
            'payload'              => $payload,
            'actor_id'             => $actorId,
            'actor_name'           => $actorName ?? 'System',
            'actor_role'           => $actorRole ?? 'seller',
            'notes'                => $notes,
            'created_at'           => now(),
        ]);
    }
}
