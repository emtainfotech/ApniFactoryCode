<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transection extends Model
{
    use HasFactory;

    protected $table = 'transections';

    protected $fillable = [
        'order_id',
        'parent_order_id',
        'payment_ref',
        'gateway_name',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_event_id',
        'amount',
        'currency',
        'gateway_signature',
        'initiated_at',
        'callback_received_at',
        'verified_at',
        'gateway_captured_at',
        'verification_status',
        'reconciliation_status',
        'reconciled_at',
        'reconciled_by_user_id',
        'reconciliation_notes',
        'failure_code',
        'failure_reason',
        'idempotency_key',
        'order_no',
        'customer_id',
        'user_id',
        'status',
        'txnid',
        'txndetail',
        'txnresponse',
        'txnmethod',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'initiated_at'         => 'datetime',
        'callback_received_at' => 'datetime',
        'verified_at'          => 'datetime',
        'gateway_captured_at'  => 'datetime',
        'reconciled_at'        => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function parentOrder()
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(PaymentAuditLog::class, 'transection_id')->orderBy('id', 'desc');
    }

    public function isSuccessful(): bool
    {
        return in_array(strtolower($this->status), ['success', 'successful', 'captured']);
    }

    public function isPending(): bool
    {
        return in_array(strtolower($this->status), ['pending', 'initiated']);
    }

    public function isFailed(): bool
    {
        return in_array(strtolower($this->status), ['failed', 'failure', 'cancelled']);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isReconciled(): bool
    {
        return $this->reconciliation_status === 'matched';
    }
}
