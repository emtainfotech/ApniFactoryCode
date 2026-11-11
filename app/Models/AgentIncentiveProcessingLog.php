<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentIncentiveProcessingLog extends Model
{
    use HasFactory;

    protected $table = 'agent_incentive_processing_logs';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_SKIPPED   = 'skipped';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_RETRIED   = 'retried';

    protected $fillable = [
        'event_key',
        'event_type',
        'parent_order_id',
        'order_id',
        'payload',
        'status',
        'attempts',
        'error_message',
        'last_attempted_at',
        'processed_at',
    ];

    protected $casts = [
        'payload'           => 'array',
        'attempts'          => 'integer',
        'last_attempted_at' => 'datetime',
        'processed_at'      => 'datetime',
    ];
}

