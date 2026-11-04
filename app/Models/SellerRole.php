<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerRole extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'label', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    public function permissions()
    {
        return $this->belongsToMany(SellerPermission::class, 'seller_role_permissions', 'seller_role_id', 'seller_permission_id')
                    ->withTimestamps();
    }

    public function staff()
    {
        return $this->hasMany(SellerStaff::class);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }
}

