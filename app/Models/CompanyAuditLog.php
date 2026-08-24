<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAuditLog extends Model
{
    use HasFactory;

    protected $table = 'company_audit_logs';

    protected $fillable = [
        'company_id',
        'user_id',
        'actor_name',
        'actor_role',
        'action_type',
        'title',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper to easily record a company change log
     */
    public static function logChange(
        $companyId,
        $actionType,
        $title,
        $description = null,
        $oldValues = null,
        $newValues = null,
        $userId = null,
        $actorName = null,
        $actorRole = null
    ) {
        $user = $userId ? User::find($userId) : auth()->user();
        
        return self::create([
            'company_id'  => $companyId,
            'user_id'     => $user ? $user->id : $userId,
            'actor_name'  => $actorName ?: ($user ? $user->name : 'System'),
            'actor_role'  => $actorRole ?: ($user && $user->type === 'admin' ? 'admin' : 'seller'),
            'action_type' => $actionType,
            'title'       => $title,
            'description' => $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()->ip() ?? '127.0.0.1',
        ]);
    }
}
