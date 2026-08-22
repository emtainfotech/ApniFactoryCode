<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
      protected $fillable = [
        'name',
        'code',
        'title',
        'description',
        'type',
        'price',
        'expiry',
        'status',
        'image','couponon','couponapplyon','startdate'
    ];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function usercmp(){
        return $this->belongsTo(Company::class);
    }
}
