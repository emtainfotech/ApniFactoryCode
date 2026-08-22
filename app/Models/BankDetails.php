<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    use HasFactory;
     protected $fillable = [
       'accountholder', 'accountno', 'bankname', 'branch', 'ifsc', 'isprimary', 'user_id', 'status', 'company_id'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
