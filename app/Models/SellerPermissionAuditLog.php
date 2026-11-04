<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPermissionAuditLog extends Model
{
    protected $fillable = [
        'company_id', 'acting_user_id', 'target_user_id',
        'action', 'subject', 'old_value', 'new_value',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function actingUser()
    {
        return $this->belongsTo(User::class, 'acting_user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public static function log(
        int $companyId,
        int $actingUserId,
        string $action,
        ?int $targetUserId = null,
        ?string $subject = null,
        mixed $oldValue = null,
        mixed $newValue = null
    ): self {
        return self::create([
            'company_id'     => $companyId,
            'acting_user_id' => $actingUserId,
            'target_user_id' => $targetUserId,
            'action'         => $action,
            'subject'        => $subject,
            'old_value'      => is_array($oldValue) ? $oldValue : ($oldValue !== null ? ['value' => $oldValue] : null),
            'new_value'      => is_array($newValue) ? $newValue : ($newValue !== null ? ['value' => $newValue] : null),
            'ip_address'     => request() ? request()->ip() : null,
            'user_agent'     => request() ? request()->userAgent() : null,
        ]);
    }
}

