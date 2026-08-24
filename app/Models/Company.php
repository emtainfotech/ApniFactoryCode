<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
      protected $fillable = ['name', 'status',  'user_id', 'email', 'mobile', 'maincategory_id', 'gst', 'crn', 'minordervalue', 'photo', 'city','state','pincode','comission'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function bank(){
         return $this->hasMany(BankDetails::class);
    }
    
    public function maincategory(){
        return $this->belongsTo(MainCategory::class);
    }

    public function auditLogs(){
        return $this->hasMany(CompanyAuditLog::class, 'company_id')->orderBy('id', 'desc');
    }

    public function priceAdjustments(){
        return $this->hasMany(PaintPriceAdjustment::class, 'user_id', 'user_id')->orderBy('id', 'desc');
    }
}
