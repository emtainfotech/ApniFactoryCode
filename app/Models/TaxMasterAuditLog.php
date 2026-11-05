<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxMasterAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'tax_master_audit_logs';

    protected $fillable = [
        'tax_master_id',
        'user_id',
        'action',
        'actor_name',
        'actor_role',
        'previous_payload',
        'new_payload',
        'notes',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'previous_payload' => 'array',
        'new_payload'      => 'array',
        'created_at'       => 'datetime',
        'tax_master_id'    => 'integer',
        'user_id'          => 'integer',
    ];

    public function taxMaster(): BelongsTo
    {
        return $this->belongsTo(TaxMaster::class, 'tax_master_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function logAction(
        int $taxMasterId,
        string $action,
        ?array $prev = null,
        ?array $new = null,
        ?int $userId = null,
        ?string $actorName = null,
        ?string $actorRole = null,
        ?string $notes = null
    ): self {
        return self::create([
            'tax_master_id'    => $taxMasterId,
            'user_id'          => $userId,
            'action'           => $action,
            'actor_name'       => $actorName ?? 'System',
            'actor_role'       => $actorRole ?? 'admin',
            'previous_payload' => $prev,
            'new_payload'      => $new,
            'notes'            => $notes,
            'ip_address'       => request()->ip() ?? '127.0.0.1',
            'created_at'       => now(),
        ]);
    }
}

