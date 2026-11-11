<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationSyncEvent extends Model
{
    use HasFactory;

    protected $table = 'integration_sync_events';

    public const DIRECTION_OUTBOUND    = 'outbound';
    public const DIRECTION_INBOUND     = 'inbound';
    public const DIRECTION_PROJECTION  = 'internal_projection';

    public const STATUS_PENDING           = 'pending';
    public const STATUS_PROCESSING        = 'processing';
    public const STATUS_PROCESSED         = 'processed';
    public const STATUS_FAILED            = 'failed';
    public const STATUS_RETRYING          = 'retrying';
    public const STATUS_DEAD_LETTER       = 'dead_letter';
    public const STATUS_SKIPPED_DISABLED  = 'skipped_disabled';
    public const STATUS_REJECTED_CONFLICT = 'rejected_conflict';

    protected $fillable = [
        'event_ref',
        'idempotency_key',
        'direction',
        'source_channel',
        'target_channel',
        'entity_type',
        'canonical_id',
        'canonical_ref',
        'action',
        'payload_json',
        'payload_hash',
        'status',
        'attempts',
        'max_attempts',
        'next_retry_at',
        'last_error',
        'last_http_status',
        'processed_at',
        'actor_user_id',
        'actor_channel',
        'conflict_policy',
    ];

    protected $casts = [
        'canonical_id'     => 'integer',
        'payload_json'     => 'array',
        'attempts'         => 'integer',
        'max_attempts'     => 'integer',
        'last_http_status' => 'integer',
        'next_retry_at'    => 'datetime',
        'processed_at'     => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(IntegrationSyncAuditLog::class, 'sync_event_id')->orderByDesc('id');
    }
}

