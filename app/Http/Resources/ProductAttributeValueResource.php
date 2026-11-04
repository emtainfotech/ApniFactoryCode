<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeValueResource extends JsonResource
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
            'attribute_name' => $this->attribute->name ?? null,
            'attribute_code' => $this->attribute->code ?? null,
            'attribute_type' => $this->attribute->type ?? null,
            'attribute_unit' => $this->attribute->unit ?? null,
            'attribute_option_id' => $this->attribute_option_id,
            'value' => $this->resolved_value,
        ];
    }
}
