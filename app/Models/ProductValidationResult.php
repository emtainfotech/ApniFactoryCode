<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductValidationResult extends Model
{
    use HasFactory;

    protected $table = 'product_validation_results';

    protected $fillable = [
        'product_id',
        'company_id',
        'user_id',
        'status',
        'passed_checks',
        'failed_checks',
        'warnings',
        'error_count',
        'warning_count',
    ];

    protected $casts = [
        'passed_checks' => 'array',
        'failed_checks' => 'array',
        'warnings'      => 'array',
        'error_count'   => 'integer',
        'warning_count' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isPassed(): bool
    {
        return in_array($this->status, ['passed', 'passed_with_warnings'], true);
    }

    public function hasBlockingErrors(): bool
    {
        return $this->status === 'failed' || $this->error_count > 0;
    }
}

