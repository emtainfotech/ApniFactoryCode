<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceRevision extends Model
{
    use HasFactory;

    protected $table = 'price_revisions';

    protected $fillable = [
        'revision_code',
        'company_id',
        'created_by_user_id',
        'approved_by_user_id',
        'revision_method',
        'revision_direction',
        'revision_value',
        'price_basis_unit',
        'rounding_rule',
        'status',
        'approval_required',
        'effective_at',
        'activated_at',
        'cancelled_at',
        'reason',
        'rejection_reason',
        'selection_criteria',
        'preview_token',
        'total_skus',
        'affected_skus_count',
        'incompatible_skus_count',
        'failed_skus_count',
        'idempotency_key',
        'execution_log',
    ];

    protected $casts = [
        'selection_criteria' => 'array',
        'execution_log' => 'array',
        'effective_at' => 'datetime',
        'activated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'approval_required' => 'boolean',
        'revision_value' => 'float',
        'total_skus' => 'integer',
        'affected_skus_count' => 'integer',
        'incompatible_skus_count' => 'integer',
        'failed_skus_count' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceRevisionItem::class, 'price_revision_id');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }
}
