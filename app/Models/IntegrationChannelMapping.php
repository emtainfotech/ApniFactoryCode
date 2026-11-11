<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationChannelMapping extends Model
{
    use HasFactory;

    protected $table = 'integration_channel_mappings';

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_CONFLICT = 'conflict';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'entity_type',
        'canonical_id',
        'canonical_ref',
        'channel',
        'external_id',
        'external_ref',
        'payload_hash',
        'mapping_status',
        'last_synced_at',
    ];

    protected $casts = [
        'canonical_id'   => 'integer',
        'last_synced_at' => 'datetime',
    ];
}

