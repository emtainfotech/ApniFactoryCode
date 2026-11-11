<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationReconciliationRecord extends Model
{
    use HasFactory;

    protected $table = 'integration_reconciliation_records';

    public const TYPE_PRICE_DRIFT            = 'price_drift';
    public const TYPE_STOCK_DRIFT            = 'stock_drift';
    public const TYPE_STATUS_DRIFT           = 'status_drift';
    public const TYPE_UNMAPPED_LEGACY_SKU    = 'unmapped_legacy_sku';
    public const TYPE_CONCURRENT_CONFLICT    = 'conflicting_concurrent_update';
    public const TYPE_UNAUTHORIZED_OVERRIDE  = 'unauthorized_field_override';
    public const TYPE_CRM_MISMATCH           = 'crm_sync_mismatch';
    public const TYPE_PAYMENT_MISMATCH       = 'payment_webhook_mismatch';

    public const STATUS_OPEN          = 'open';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_RECONCILED    = 'reconciled';
    public const STATUS_IGNORED       = 'ignored';
    public const STATUS_ESCALATED     = 'escalated';

    protected $fillable = [
        'reconciliation_ref',
        'entity_type',
        'canonical_id',
        'canonical_ref',
        'source_channel',
        'target_channel',
        'mismatch_type',
        'authoritative_snapshot',
        'channel_snapshot',
        'discrepancy_summary',
        'severity',
        'status',
        'resolution_action',
        'resolution_notes',
        'resolved_by_user_id',
        'resolved_at',
    ];

    protected $casts = [
        'canonical_id'           => 'integer',
        'authoritative_snapshot' => 'array',
        'channel_snapshot'       => 'array',
        'resolved_at'            => 'datetime',
    ];

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(IntegrationSyncAuditLog::class, 'reconciliation_record_id')->orderByDesc('id');
    }
}

