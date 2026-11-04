<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
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
            'image' => $this->image ? url($this->image) : null,
            'company_id' => $this->company_id,
            'company_name' => $this->company->name ?? null,
            'category_id' => $this->category_id,
            'status' => $this->status,
        ];
    }
}

