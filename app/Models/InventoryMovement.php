<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'seller_listing_id',
        'seller_user_id',
        'movement_type',
        'quantity_change',
        'previous_stock',
        'new_stock',
        'reference_order_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    public function sellerListing()
    {
        return $this->belongsTo(SellerListing::class, 'seller_listing_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
