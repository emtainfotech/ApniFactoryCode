<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'image' => $this->image ? url($this->image) : null,
            'maincategory_id' => $this->maincategory_id,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'subcategories' => SubcategoryResource::collection($this->whenLoaded('subcategories')),
            'attributes_count' => $this->when(isset($this->category_attributes_count), $this->category_attributes_count),
        ];
    }
}

