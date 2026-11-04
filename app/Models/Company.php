<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'status', 'user_id', 'email', 'mobile', 'maincategory_id',
        'gst', 'crn', 'minordervalue', 'photo', 'city', 'state', 'pincode', 'comission'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function staff(){
        return $this->hasMany(SellerStaff::class);
    }

    public function permissionAuditLogs(){
        return $this->hasMany(SellerPermissionAuditLog::class)->orderBy('id', 'desc');
    }

    public function catalogueExports(){
        return $this->hasMany(SellerCatalogueExport::class)->orderBy('id', 'desc');
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

    public function getStatusAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return in_array(strtolower($value ?? ''), ['active', '1']) ? 1 : 0;
        }
        return $value;
    }

    public function getMinordervalueAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return (int) round((float) ($value ?? 0));
        }
        return $value;
    }

    public function getPincodeAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return (int) ($value ?? 0);
        }
        return $value;
    }

    public function getComissionAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return (int) round((float) ($value ?? 0));
        }
        return $value;
    }
}
