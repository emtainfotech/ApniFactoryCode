<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerCatalogueImport extends Model
{
    use HasFactory;

    protected $table = 'seller_catalogue_imports';

    protected $fillable = [
        'company_id',
        'user_id',
        'original_filename',
        'file_path',
        'file_size',
        'error_report_path',
        'import_format',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'created_products',
        'updated_products',
        'created_variants',
        'updated_variants',
        'created_listings',
        'updated_listings',
        'failed_rows',
        'validation_summary',
        'error_summary',
        'started_at',
        'completed_at',
        'ip_address',
    ];

    protected $casts = [
        'validation_summary' => 'array',
        'error_summary'      => 'array',
        'total_rows'         => 'integer',
        'valid_rows'         => 'integer',
        'invalid_rows'       => 'integer',
        'created_products'   => 'integer',
        'updated_products'   => 'integer',
        'created_variants'   => 'integer',
        'updated_variants'   => 'integer',
        'created_listings'   => 'integer',
        'updated_listings'   => 'integer',
        'failed_rows'        => 'integer',
        'file_size'          => 'integer',
        'started_at'         => 'datetime',
        'completed_at'       => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'partial_success'], true);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'validation_failed'], true);
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, ['validating', 'processing'], true);
    }

    public function canConfirm(): bool
    {
        return $this->status === 'validated' && $this->valid_rows > 0;
    }

    public function getErrorReportUrlAttribute(): ?string
    {
        if (!$this->error_report_path) {
            return null;
        }
        return route('seller.catalogue.import.errors', ['id' => $this->id]);
    }

    public function getApiErrorReportUrlAttribute(): ?string
    {
        if (!$this->error_report_path) {
            return null;
        }
        return url("/api/v2/seller/catalogue/imports/{$this->id}/errors");
    }
}
