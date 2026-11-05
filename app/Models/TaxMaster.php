<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxMaster extends Model
{
    use HasFactory;

    protected $table = 'tax_masters';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INACTIVE = 'inactive';

    public const TYPE_GOODS    = 'goods';
    public const TYPE_SERVICES = 'services';

    protected $fillable = [
        'hsn_code',
        'description',
        'gst_rate',
        'type',
        'effective_from',
        'effective_to',
        'source_reference',
        'approval_status',
        'last_verified_at',
        'verified_by_user_id',
        'created_by_user_id',
        'approved_by_user_id',
        'rejection_reason',
        'version',
        'previous_version_id',
    ];

    protected $casts = [
        'gst_rate'            => 'decimal:2',
        'effective_from'      => 'date',
        'effective_to'        => 'date',
        'last_verified_at'    => 'datetime',
        'version'             => 'integer',
        'created_by_user_id'  => 'integer',
        'approved_by_user_id' => 'integer',
        'verified_by_user_id' => 'integer',
    ];

    /**
     * Scope for approved records.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }

    /**
     * Scope for records effective on a specific date.
     */
    public function scopeEffectiveOn(Builder $query, string $date): Builder
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            });
    }

    /**
     * Validate standard Indian GST HSN/SAC format.
     * HSN: 4, 6, or 8 digits.
     * SAC: 6 digits starting with 99.
     */
    public static function isValidHsnOrSac(string $code): bool
    {
        $code = trim($code);
        // SAC validation: 6 digits starting with 99
        if (preg_match('/^99\d{4}$/', $code)) {
            return true;
        }
        // HSN validation: 4, 6, or 8 digits
        return (bool) preg_match('/^(\d{4}|\d{6}|\d{8})$/', $code);
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

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
        return $this->hasMany(TaxMasterAuditLog::class, 'tax_master_id')->orderBy('id', 'desc');
    }
}

