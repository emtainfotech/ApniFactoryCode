<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'image' => $this->image ? url($this->image) : null,
            'brand' => [
                'id' => $this->brand_id,
                'name' => $this->brand->name ?? null,
            ],
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category->name ?? null,
            ],
            'subcategory' => [
                'id' => $this->subcategory_id,
                'name' => $this->subcategory->name ?? null,
            ],
            'starting_price' => $lowestListing ? (float) $lowestListing->selling_price : null,
            'mrp' => $lowestListing ? (float) $lowestListing->mrp : null,
            'variants_count' => $this->variants_count ?? $this->variants->count(),
            'status' => $this->status,
        ];
    }
}

