<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $table="wallet";
      protected $fillable = [
        'user_id',
        'order_id',
        'orderno',
        'value',
        'commission',
        'refundtobuyer',
        'debit',
        'credit',
        'balance',
        'addby',
        'msg',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
