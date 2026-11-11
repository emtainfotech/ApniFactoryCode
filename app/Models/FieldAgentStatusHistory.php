<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldAgentStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'field_agent_status_history';

    public $timestamps = false;

    protected $fillable = [
        'field_agent_id',
        'action',
        'previous_status',
        'new_status',
        'actor_user_id',
        'actor_name',
        'actor_role',
        'reason',
        'previous_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'previous_values' => 'array',
        'new_values'      => 'array',
        'created_at'      => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Field agent status audit logs are immutable and cannot be modified.');
        });

        static::deleting(function () {
            throw new \LogicException('Field agent status audit logs are immutable and cannot be deleted.');
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FieldAgent::class, 'field_agent_id');
    }

    public static function logAction(
        FieldAgent $agent,
        string $action,
        ?User $actor = null,
        ?string $previousStatus = null,
        ?string $newStatus = null,
        ?string $reason = null,
        ?array $previousValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'field_agent_id'  => $agent->id,
            'action'          => $action,
            'previous_status' => $previousStatus,
            'new_status'      => $newStatus,
            'actor_user_id'   => $actor?->id,
            'actor_name'      => $actor ? ($actor->name ?? $actor->email) : 'System',
            'actor_role'      => $actor && $actor->isAdmin() ? 'admin' : 'system',
            'reason'          => $reason,
            'previous_values' => $previousValues,
            'new_values'      => $newValues,
            'ip_address'      => request() ? request()->ip() : null,
            'user_agent'      => request() ? request()->userAgent() : null,
            'created_at'      => now(),
        ]);
    }
}

