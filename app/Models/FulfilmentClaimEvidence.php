<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FulfilmentClaimEvidence extends Model
{
    use HasFactory;

    protected $table = 'fulfilment_claim_evidence';

    public $timestamps = false; // Only created_at is present

    protected $fillable = [
        'fulfilment_claim_id',
        'uploader_user_id',
        'uploader_role',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'file_size'  => 'integer',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(FulfilmentClaim::class, 'fulfilment_claim_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_user_id');
    }

    public function getUrlAttribute(): string
    {
        return asset($this->file_path);
    }
}
