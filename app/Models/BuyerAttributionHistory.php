<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerAttributionHistory extends Model
{
    use HasFactory;

    protected $table = 'buyer_attribution_history';

    public $timestamps = false;

    protected $fillable = [
        'buyer_agent_attribution_id',
        'customer_id',
        'field_agent_id',
        'action',
        'previous_status',
        'new_status',
        'previous_agent_id',
        'new_agent_id',
        'actor_user_id',
        'actor_name',
        'actor_role',
        'reason',
        'evidence_reference',
        'payload',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Attribution history records are immutable and cannot be modified.');
        });

        static::deleting(function () {
            throw new \LogicException('Attribution history records are immutable and cannot be deleted.');
        });
    }

    public function attribution(): BelongsTo
    {
        return $this->belongsTo(BuyerAgentAttribution::class, 'buyer_agent_attribution_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FieldAgent::class, 'field_agent_id');
    }

    public static function logAction(
        BuyerAgentAttribution $attribution,
        string $action,
        User $actor,
        ?string $previousStatus = null,
        ?string $newStatus = null,
        ?int $previousAgentId = null,
        ?int $newAgentId = null,
        ?string $reason = null,
        ?string $evidenceRef = null,
        ?array $payload = null
    ): self {
        return self::create([
            'buyer_agent_attribution_id' => $attribution->id,
            'customer_id'                => $attribution->customer_id,
            'field_agent_id'             => $attribution->field_agent_id,
            'action'                     => $action,
            'previous_status'            => $previousStatus,
            'new_status'                 => $newStatus,
            'previous_agent_id'          => $previousAgentId,
            'new_agent_id'               => $newAgentId,
            'actor_user_id'              => $actor->id,
            'actor_name'                 => $actor->name ?? $actor->email,
            'actor_role'                 => $actor->isAdmin() ? 'admin' : 'user',
            'reason'                     => $reason,
            'evidence_reference'         => $evidenceRef,
            'payload'                    => $payload,
            'ip_address'                 => request() ? request()->ip() : null,
            'user_agent'                 => request() ? request()->userAgent() : null,
            'created_at'                 => now(),
        ]);
    }
}

