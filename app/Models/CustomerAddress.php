<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;
    
      protected $fillable = [
        'address_id',
        'name',
        'customer_id',
        'landmark1',
        'landmark2',
        'city',
        'state',
        'country',
        'pincode',
        'phoneno',
        'location',
        'identityname'
    ];
    
    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
