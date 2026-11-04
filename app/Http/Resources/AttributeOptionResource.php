<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeOptionResource extends JsonResource
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
            'attribute_id' => $this->attribute_id,
            'label' => $this->label,
            'value' => $this->value,
            'color_hex' => $this->color_hex,
            'sort_order' => $this->sort_order,
            'is_default' => (bool) $this->is_default,
            'status' => $this->status,
        ];
    }
}

