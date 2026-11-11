<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationSyncAuditLog extends Model
{
    use HasFactory;

    protected $table = 'integration_sync_audit_logs';

    protected $fillable = [
        'sync_event_id',
        'reconciliation_record_id',
        'entity_type',
        'canonical_ref',
        'action',
        'actor_user_id',
        'actor_role',
        'channel',
        'before_state',
        'after_state',
        'notes',
        'ip_address',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state'  => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Integration sync audit logs are immutable.');
        });
        static::deleting(function () {
            throw new \LogicException('Integration sync audit logs cannot be deleted.');
        });
    }
}

