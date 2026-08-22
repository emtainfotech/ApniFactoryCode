<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model
{
  

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'lastlogin',
        'type',
        'status',
        'deviceid',
        'location',
        'followers',
        'followings',
        'image',
        'otp',
        'regby'
    ];

}

