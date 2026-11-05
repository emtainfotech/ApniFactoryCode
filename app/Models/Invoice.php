<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    public const TYPE_GOODS_TAX_INVOICE           = 'goods_tax_invoice';
    public const TYPE_PLATFORM_COMMISSION_INVOICE = 'platform_commission_invoice';

    public const ISSUER_SELLER   = 'seller';
    public const ISSUER_PLATFORM = 'platform';

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_ISSUED    = 'issued';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_AMENDED   = 'amended';

    protected $fillable = [
        'invoice_no',
        'invoice_type',
        'parent_order_id',
        'order_id',
        'seller_user_id',
        'customer_id',
        'company_id',
        'issuer_type',
        'issuer_name',
        'issuer_gstin',
        'issuer_address',
        'issuer_state',
        'recipient_name',
        'recipient_gstin',
        'recipient_address',
        'recipient_state',
        'place_of_supply',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_tax_amount',
        'round_off',
        'total_amount',
        'status',
        'issued_at',
        'document_path',
        'is_regenerated',
    ];

    protected $casts = [
        'taxable_amount'   => 'decimal:2',
        'cgst_amount'      => 'decimal:2',
        'sgst_amount'      => 'decimal:2',
        'igst_amount'      => 'decimal:2',
        'total_tax_amount' => 'decimal:2',
        'round_off'        => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'issued_at'        => 'datetime',
        'is_regenerated'   => 'boolean',
        'parent_order_id'  => 'integer',
        'order_id'         => 'integer',
        'seller_user_id'   => 'integer',
        'customer_id'      => 'integer',
        'company_id'       => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function isGoodsInvoice(): bool
    {
        return $this->invoice_type === self::TYPE_GOODS_TAX_INVOICE;
    }

    public function isCommissionInvoice(): bool
    {
        return $this->invoice_type === self::TYPE_PLATFORM_COMMISSION_INVOICE;
    }
}

