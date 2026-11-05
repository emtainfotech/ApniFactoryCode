<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $guarded = [];

    protected $casts = [
        'grandtotal'         => 'decimal:2',
        'netamount'          => 'decimal:2',
        'taxamount'          => 'decimal:2',
        'admincouponamount'  => 'decimal:2',
        'sellercouponamount' => 'decimal:2',
        'paid_at'            => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transections()
    {
        return $this->hasMany(Transection::class, 'order_id')->orderBy('id', 'desc');
    }

    public function latestTransection()
    {
        return $this->hasOne(Transection::class, 'order_id')->latestOfMany();
    }

    public function parentOrder()
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function childOrders()
    {
        return $this->hasMany(Order::class, 'parent_order_id');
    }

    public function isPaid(): bool
    {
        return strtolower($this->payment_status) === 'paid';
    }

    /**
     * Mark parent order and all child fulfilment orders as Paid.
     */
    public function markAsPaid(Transection $transaction): void
    {
        $this->update([
            'payment_status' => 'Paid',
            'paid_at'        => now(),
        ]);

        // Propagate payment status to child seller fulfilment orders
        $childOrders = $this->childOrders()->get();
        foreach ($childOrders as $child) {
            $child->update([
                'payment_status' => 'Paid',
                'paid_at'        => now(),
            ]);
        }
    }
}
