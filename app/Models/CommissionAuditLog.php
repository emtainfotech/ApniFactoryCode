<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionAuditLog extends Model
{
    use HasFactory;

    protected $table = 'commission_audit_logs';

    protected $fillable = [
        'commission_rule_id',
        'actor_user_id',
        'event_type',
        'previous_values',
        'new_values',
        'notes',
    ];

    protected $casts = [
        'previous_values' => 'array',
        'new_values'      => 'array',
        'commission_rule_id' => 'integer',
        'actor_user_id'      => 'integer',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
