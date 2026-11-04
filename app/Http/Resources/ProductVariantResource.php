<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $primary = $this->primaryListing;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_sku' => $this->variant_sku,
            'variant_name' => $this->variant_name,
            'pack_litres' => $this->pack_litres,
            'shade_card_id' => $this->shade_card_id,
            'box_packing_id' => $this->box_packing_id,
            'image' => $this->image ? url($this->image) : null,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'buy_box_price' => $primary ? (float) $primary->selling_price : null,
            'mrp' => $primary ? (float) $primary->mrp : null,
            'in_stock' => $primary ? ($primary->available_stock > 0) : false,
            'primary_listing' => $primary ? new PublicListingResource($primary) : null,
            'attributes' => ProductAttributeValueResource::collection($this->whenLoaded('attributeValues')),
        ];
    }
}

