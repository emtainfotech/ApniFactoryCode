<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionRule extends Model
{
    use HasFactory;

    protected $table = 'commission_rules';

    protected $fillable = [
        'rule_code',
        'name',
        'scope_type',
        'scope_id',
        'commission_percentage',
        'fixed_fee',
        'calculation_basis',
        'gst_rate',
        'is_gst_inclusive',
        'effective_from',
        'effective_to',
        'status',
        'created_by_user_id',
        'approved_by_user_id',
        'agreement_ref',
        'remarks',
        'rejection_reason',
    ];

    protected $casts = [
        'commission_percentage' => 'float',
        'fixed_fee'             => 'float',
        'gst_rate'              => 'float',
        'is_gst_inclusive'      => 'boolean',
        'effective_from'        => 'datetime',
        'effective_to'          => 'datetime',
        'scope_id'              => 'integer',
        'created_by_user_id'    => 'integer',
        'approved_by_user_id'   => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CommissionAuditLog::class, 'commission_rule_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(OrderCommissionSnapshot::class, 'commission_rule_id');
    }

    /**
     * Scope to find active and currently effective rules at a given timestamp.
     */
    public function scopeActive($query, $at = null)
    {
        $timestamp = $at ? Carbon::parse($at) : now();
        return $query->where('status', 'active')
            ->where('effective_from', '<=', $timestamp)
            ->where(function ($q) use ($timestamp) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $timestamp);
            });
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeForScope($query, string $type, ?int $id = null)
    {
        $query->where('scope_type', $type);
        if ($id !== null) {
            $query->where('scope_id', $id);
        } else {
            $query->whereNull('scope_id');
        }
        return $query;
    }

    /**
     * Check if this rule is currently effective at a specified time.
     */
    public function isCurrentlyEffective($at = null): bool
    {
        $timestamp = $at ? Carbon::parse($at) : now();
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->effective_from > $timestamp) {
            return false;
        }
        if ($this->effective_to && $this->effective_to < $timestamp) {
            return false;
        }
        return true;
    }

    /**
     * Resolve target scope display label.
     */
    public function getScopeLabelAttribute(): string
    {
        switch ($this->scope_type) {
            case 'sku':
                $listing = SellerListing::with('product')->find($this->scope_id);
                return $listing ? "SKU: {$listing->seller_sku} ({$listing->product->name})" : "SKU #{$this->scope_id}";
            case 'seller':
                $company = Company::find($this->scope_id);
                return $company ? "Seller: {$company->name}" : "Seller #{$this->scope_id}";
            case 'subcategory':
                $subcat = SubCatgory::find($this->scope_id);
                return $subcat ? "Subcategory: {$subcat->name}" : "Subcategory #{$this->scope_id}";
            case 'category':
                $cat = Category::find($this->scope_id);
                return $cat ? "Category: {$cat->name}" : "Category #{$this->scope_id}";
            case 'global':
            default:
                return 'Global Marketplace Default';
        }
    }
}
