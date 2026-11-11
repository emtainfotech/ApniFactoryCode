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

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    public function attributions()
    {
        return $this->hasMany(BuyerAgentAttribution::class, 'customer_id')->orderBy('id', 'desc');
    }

    public function activeAttribution()
    {
        return $this->hasOne(BuyerAgentAttribution::class, 'customer_id')
            ->whereNotNull('active_customer_lock')
            ->latestOfMany();
    }

    public function attributionHistory()
    {
        return $this->hasMany(BuyerAttributionHistory::class, 'customer_id')->orderBy('id', 'desc');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function eligibleOrders()
    {
        return $this->hasMany(AgentEligibleOrder::class, 'customer_id')->orderBy('id', 'asc');
    }

    public function incentiveLedgers()
    {
        return $this->hasMany(AgentIncentiveLedger::class, 'customer_id')->orderBy('id', 'desc');
    }

}

