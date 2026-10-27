<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfqRequest extends Model
{
    use HasFactory;

    protected $table = 'rfq_requests';

    protected $fillable = [
        'rfq_number',
        'customer_id',
        'seller_user_id',
        'product_id',
        'category_name',
        'product_title',
        'target_quantity',
        'unit_of_measure',
        'target_unit_price',
        'delivery_pincode',
        'delivery_deadline',
        'technical_specifications',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function quotes()
    {
        return $this->hasMany(RfqQuote::class, 'rfq_request_id');
    }
}
