<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentIncentiveAuditLog extends Model
{
    use HasFactory;

    protected $table = 'agent_incentive_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
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
            throw new \LogicException('Incentive audit log records are append-only and immutable.');
        });

        static::deleting(function () {
            throw new \LogicException('Incentive audit log records cannot be deleted.');
        });
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function logAction(
        string $entityType,
        int $entityId,
        string $action,
        ?User $actor = null,
        ?string $prevStatus = null,
        ?string $newStatus = null,
        ?string $reason = null,
        ?array $prevValues = null,
        ?array $newValues = null,
        string $actorRole = 'admin'
    ): self {
        return self::create([
            'entity_type'     => $entityType,
            'entity_id'       => $entityId,
            'action'          => $action,
            'previous_status' => $prevStatus,
            'new_status'      => $newStatus,
            'actor_user_id'   => $actor?->id,
            'actor_name'      => $actor ? ($actor->name ?? $actor->email) : 'System Engine',
            'actor_role'      => $actor ? $actorRole : 'system',
            'reason'          => $reason,
            'previous_values' => $prevValues,
            'new_values'      => $newValues,
            'ip_address'      => app()->runningInConsole() ? 'cli' : request()->ip(),
            'user_agent'      => app()->runningInConsole() ? 'artisan' : substr((string)request()->userAgent(), 0, 250),
            'created_at'      => now(),
        ]);
    }
}

