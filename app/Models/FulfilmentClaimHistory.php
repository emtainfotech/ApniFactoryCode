<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FulfilmentClaimHistory extends Model
{
    use HasFactory;

    protected $table = 'fulfilment_claim_history';

    public $timestamps = false; // Only created_at is present

    protected $fillable = [
        'fulfilment_claim_id',
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
        'created_at' => 'datetime',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(FulfilmentClaim::class, 'fulfilment_claim_id');
    }

    /**
     * Helper to log an immutable audit trail entry.
     */
    public static function logAction(
        FulfilmentClaim $claim,
        string $action,
        ?int $actorId,
        string $actorRole,
        string $actorName,
        ?string $prevStatus,
        ?string $newStatus,
        ?array $payload = null,
        ?string $notes = null
    ): self {
        return self::create([
            'fulfilment_claim_id' => $claim->id,
            'action'              => $action,
            'actor_id'            => $actorId,
            'actor_name'          => $actorName,
            'actor_role'          => $actorRole,
            'previous_status'     => $prevStatus,
            'new_status'          => $newStatus,
            'payload'             => $payload ? json_encode($payload) : null,
            'notes'               => $notes,
            'created_at'          => now(),
        ]);
    }
}

