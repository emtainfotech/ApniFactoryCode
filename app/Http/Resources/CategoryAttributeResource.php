<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryAttributeResource extends JsonResource
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
            'category_id' => $this->category_id,
            'attribute_id' => $this->attribute_id,
            'attribute_name' => $this->attribute->name ?? null,
            'attribute_code' => $this->attribute->code ?? null,
            'attribute_type' => $this->attribute->type ?? null,
            'attribute_unit' => $this->attribute->unit ?? null,
            'is_required' => (bool) $this->is_required,
            'is_filterable' => (bool) $this->is_filterable,
            'is_searchable' => (bool) $this->is_searchable,
            'is_variant_generating' => (bool) $this->is_variant_generating,
            'sort_order' => $this->sort_order,
            'validation_rules' => $this->validation_rules,
            'status' => $this->status,
            'options' => AttributeOptionResource::collection($this->whenLoaded('attribute', function() {
                return $this->attribute->options ?? collect();
            })),
        ];
    }
}

