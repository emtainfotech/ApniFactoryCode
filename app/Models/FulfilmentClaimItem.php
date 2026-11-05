<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FulfilmentClaimItem extends Model
{
    use HasFactory;

    protected $table = 'fulfilment_claim_items';

    protected $fillable = [
        'fulfilment_claim_id',
        'order_detail_id',
        'product_name',
        'sku',
        'ordered_qty',
        'received_qty',
        'affected_qty',
        'condition_notes',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(FulfilmentClaim::class, 'fulfilment_claim_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }
}
