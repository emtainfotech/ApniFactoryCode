<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lowestListing = $this->listings->where('status', 'Active')->sortBy('selling_price')->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image ? url($this->image) : null,
            'multiple_images' => is_array($this->multipleimages) ? $this->multipleimages : [],
            'brand' => [
                'id' => $this->brand_id,
                'name' => $this->brand->name ?? null,
                'image' => $this->brand && $this->brand->image ? url($this->brand->image) : null,
            ],
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category->name ?? null,
            ],
            'subcategory' => [
                'id' => $this->subcategory_id,
                'name' => $this->subcategory->name ?? null,
            ],
            'tax' => (float) ($this->tax ?? 0),
            'hsncode' => $this->hsncode,
            'buy_box_price' => $lowestListing ? (float) $lowestListing->selling_price : null,
            'mrp' => $lowestListing ? (float) $lowestListing->mrp : null,
            'status' => $this->status,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'attributes' => ProductAttributeValueResource::collection($this->whenLoaded('attributeValues')),
            'available_sellers_count' => $this->listings->where('status', 'Active')->unique('seller_user_id')->count(),
        ];
    }
}

