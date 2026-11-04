<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerPermission extends Model
{
    use HasFactory;

    protected $fillable = ['module', 'action', 'name', 'label', 'description'];

    public function roles()
    {
        return $this->belongsToMany(SellerRole::class, 'seller_role_permissions', 'seller_permission_id', 'seller_role_id')
                    ->withTimestamps();
    }
}

