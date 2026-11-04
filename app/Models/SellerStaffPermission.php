<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerStaffPermission extends Model
{
    protected $fillable = ['seller_staff_id', 'seller_permission_id', 'granted'];

    protected $casts = ['granted' => 'boolean'];

    public function staff()
    {
        return $this->belongsTo(SellerStaff::class, 'seller_staff_id');
    }

    public function permission()
    {
        return $this->belongsTo(SellerPermission::class, 'seller_permission_id');
    }
}

