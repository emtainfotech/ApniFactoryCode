<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerCatalogueBulkOperation extends Model
{
    use HasFactory;

    protected $table = 'seller_catalogue_bulk_operations';

    protected $fillable = [
        'company_id',
        'user_id',
        'operation_type',
        'target_entity',
        'selection_mode',
        'selection_criteria',
        'update_payload',
        'status',
        'total_matching',
        'eligible_count',
        'ineligible_count',
        'processed_count',
        'success_count',
        'failed_count',
        'preview_data',
        'results_summary',
        'error_summary',
        'started_at',
        'completed_at',
        'ip_address',
    ];

    protected $casts = [
        'selection_criteria' => 'array',
        'update_payload'     => 'array',
        'preview_data'       => 'array',
        'results_summary'    => 'array',
        'error_summary'      => 'array',
        'total_matching'     => 'integer',
        'eligible_count'     => 'integer',
        'ineligible_count'   => 'integer',
        'processed_count'    => 'integer',
        'success_count'      => 'integer',
        'failed_count'       => 'integer',
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

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'partial_success'], true);
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function canConfirm(): bool
    {
        return $this->status === 'previewed' && $this->eligible_count > 0;
    }
}

