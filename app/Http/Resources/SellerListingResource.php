<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SellerListingResource extends JsonResource
{
    /**
     * Transform the resource into an array for seller management.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'seller_user_id' => $this->seller_user_id,
            'company_id' => $this->company_id,
            'company_name' => $this->company->name ?? null,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name ?? null,
            'product_variant_id' => $this->product_variant_id,
            'variant_sku' => $this->variant->variant_sku ?? null,
            'variant_name' => $this->variant->variant_name ?? null,
            'seller_sku' => $this->seller_sku,
            'barcode' => $this->barcode,
            'seller_price' => (float) $this->seller_price,
            'commission_rate' => (float) $this->commission_rate,
            'selling_price' => (float) $this->selling_price,
            'mrp' => (float) $this->mrp,
            'unit_of_measurement' => $this->unit_of_measurement,
            'case_pack_quantity' => (int) $this->case_pack_quantity,
            'order_multiple' => (int) $this->order_multiple,
            'min_order_quantity' => (int) $this->min_order_quantity,
            'allow_unit_orders' => (bool) $this->allow_unit_orders,
            'allow_carton_orders' => (bool) $this->allow_carton_orders,
            'stock_quantity' => (int) $this->stock_quantity,
            'reserved_stock' => (int) ($this->reserved_stock ?: 0),
            'available_stock' => (int) $this->available_stock,
            'low_stock_threshold' => (int) ($this->low_stock_threshold ?: 10),
            'dispatch_lead_days' => (int) ($this->dispatch_lead_days ?: 2),
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}

