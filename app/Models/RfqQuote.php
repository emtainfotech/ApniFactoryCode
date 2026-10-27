<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfqQuote extends Model
{
    use HasFactory;

    protected $table = 'rfq_quotes';

    protected $fillable = [
        'rfq_request_id',
        'seller_user_id',
        'company_id',
        'offered_unit_price',
        'total_amount',
        'gst_amount',
        'shipping_charges',
        'lead_time_days',
        'valid_until',
        'seller_notes',
        'payment_terms',
        'status',
    ];

    public function request()
    {
        return $this->belongsTo(RfqRequest::class, 'rfq_request_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
