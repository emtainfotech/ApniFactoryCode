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

    public function catalogueImports(){
        return $this->hasMany(SellerCatalogueImport::class)->orderBy('id', 'desc');
    }

    public function bulkOperations(){
        return $this->hasMany(SellerCatalogueBulkOperation::class)->orderBy('id', 'desc');
    }

    public function validationResults(){
        return $this->hasMany(ProductValidationResult::class)->orderBy('id', 'desc');
    }

    public function reviewHistories(){
        return $this->hasMany(ProductReviewHistory::class)->orderBy('id', 'desc');
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

    /**
     * Get raw numeric Minimum Order Value (MOV)
     */
    public function getRawMinOrderValue(): float
    {
        return (float) ($this->getRawOriginal('minordervalue') ?? $this->attributes['minordervalue'] ?? 0.0);
    }

    /**
     * Check if a given seller subtotal satisfies this seller's configured MOV
     */
    public function isMovSatisfied(float $amount): bool
    {
        $mov = $this->getRawMinOrderValue();
        return $mov <= 0 || $amount >= $mov;
    }

    /**
     * Calculate shortfall to reach MOV
     */
    public function getMovShortfall(float $amount): float
    {
        $mov = $this->getRawMinOrderValue();
        return max(0.0, round($mov - $amount, 2));
    }

    /**
     * Calculate progress percentage toward MOV (0.0 to 100.0)
     */
    public function getMovProgress(float $amount): float
    {
        $mov = $this->getRawMinOrderValue();
        if ($mov <= 0) {
            return 100.0;
        }
        return min(100.0, round(($amount / $mov) * 100, 1));
    }
}
