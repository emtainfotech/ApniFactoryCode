<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerCatalogueExport extends Model
{
    use HasFactory;

    protected $table = 'seller_catalogue_exports';

    protected $fillable = [
        'company_id',
        'user_id',
        'export_format',
        'status',
        'file_name',
        'file_path',
        'file_size',
        'total_records',
        'filters',
        'error_message',
        'started_at',
        'completed_at',
        'expires_at',
        'ip_address',
    ];

    protected $casts = [
        'filters'      => 'array',
        'total_records'=> 'integer',
        'file_size'    => 'integer',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (!$this->isCompleted() || !$this->file_path) {
            return null;
        }
        return route('seller.catalogue.export.download', ['id' => $this->id]);
    }

    public function getApiDownloadUrlAttribute(): ?string
    {
        if (!$this->isCompleted() || !$this->file_path) {
            return null;
        }
        return url("/api/v2/seller/catalogue/exports/{$this->id}/download");
    }
}

