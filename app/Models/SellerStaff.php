<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'seller_role_id', 'invited_by',
        'is_active', 'activated_at', 'deactivated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(SellerRole::class, 'seller_role_id');
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function overridePermissions()
    {
        return $this->hasMany(SellerStaffPermission::class);
    }

    /**
     * Check if this staff member has a given permission.
     * Override permissions (grant/deny) take precedence over role defaults.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check individual overrides first
        $override = $this->overridePermissions()
            ->whereHas('permission', fn($q) => $q->where('name', $permissionName))
            ->first();

        if ($override !== null) {
            return (bool) $override->granted;
        }

        // Fall back to role permissions
        return $this->role && $this->role->hasPermission($permissionName);
    }

    /**
     * Seller owner bypass: if this staff is the company owner, all permissions granted.
     */
    public function isOwner(): bool
    {
        return $this->role && $this->role->name === 'seller_owner';
    }

    /**
     * Get all effective permissions (combining role + overrides).
     */
    public function effectivePermissions(): array
    {
        if (!$this->is_active) {
            return [];
        }

        $rolePermissions = $this->role
            ? $this->role->permissions->pluck('name')->toArray()
            : [];

        $overrides = $this->overridePermissions()->with('permission')->get();

        foreach ($overrides as $override) {
            if (!$override->permission) {
                continue;
            }
            if ($override->granted && !in_array($override->permission->name, $rolePermissions)) {
                $rolePermissions[] = $override->permission->name;
            } elseif (!$override->granted) {
                $rolePermissions = array_values(array_filter($rolePermissions, fn($p) => $p !== $override->permission->name));
            }
        }

        return array_values($rolePermissions);
    }
}

