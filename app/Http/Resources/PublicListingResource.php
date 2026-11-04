<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicListingResource extends JsonResource
{
    /**
     * Transform the resource into an array for public buyers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'listing_id' => $this->id,
            'seller_id' => $this->seller_user_id,
            'seller_name' => $this->company->name ?? ($this->seller->name ?? 'Verified Seller'),
            'seller_sku' => $this->seller_sku,
            'barcode' => $this->barcode,
            'selling_price' => (float) $this->selling_price,
            'mrp' => (float) $this->mrp,
            'discount_percentage' => $this->mrp > $this->selling_price ? round((($this->mrp - $this->selling_price) / $this->mrp) * 100, 1) : 0,
            'unit_of_measurement' => $this->unit_of_measurement ?: 'Unit',
            'min_order_quantity' => (int) ($this->min_order_quantity ?: 1),
            'order_multiple' => (int) ($this->order_multiple ?: 1),
            'case_pack_quantity' => (int) ($this->case_pack_quantity ?: 1),
            'allow_unit_orders' => (bool) $this->allow_unit_orders,
            'allow_carton_orders' => (bool) $this->allow_carton_orders,
            'in_stock' => $this->available_stock > 0,
            'stock_status' => $this->available_stock <= 0 ? 'Out of Stock' : ($this->available_stock <= $this->low_stock_threshold ? 'Low Stock' : 'In Stock'),
            'dispatch_lead_days' => (int) ($this->dispatch_lead_days ?: 2),
            'status' => $this->status,
        ];
    }
}

