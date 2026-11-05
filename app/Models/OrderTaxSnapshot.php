<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTaxSnapshot extends Model
{
    use HasFactory;

    protected $table = 'order_tax_snapshots';

    protected $fillable = [
        'order_id',
        'order_detail_id',
        'orderno',
        'seller_user_id',
        'customer_id',
        'product_id',
        'product_name',
        'sku',
        'hsn_code',
        'tax_master_id',
        'tax_description',
        'tax_rate',
        'taxable_amount',
        'is_interstate',
        'place_of_supply',
        'seller_state',
        'buyer_state',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'total_tax_amount',
        'gross_amount',
        'tax_master_snapshot',
        'calculated_at',
    ];

    protected $casts = [
        'tax_rate'            => 'decimal:2',
        'taxable_amount'      => 'decimal:2',
        'is_interstate'       => 'boolean',
        'cgst_rate'           => 'decimal:2',
        'cgst_amount'         => 'decimal:2',
        'sgst_rate'           => 'decimal:2',
        'sgst_amount'         => 'decimal:2',
        'igst_rate'           => 'decimal:2',
        'igst_amount'         => 'decimal:2',
        'total_tax_amount'    => 'decimal:2',
        'gross_amount'        => 'decimal:2',
        'tax_master_snapshot' => 'array',
        'calculated_at'       => 'datetime',
        'order_id'            => 'integer',
        'order_detail_id'     => 'integer',
        'seller_user_id'      => 'integer',
        'customer_id'         => 'integer',
        'product_id'          => 'integer',
        'tax_master_id'       => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function taxMaster(): BelongsTo
    {
        return $this->belongsTo(TaxMaster::class, 'tax_master_id');
    }
}

