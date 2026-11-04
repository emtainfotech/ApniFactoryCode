<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
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
            'code' => $this->code,
            'type' => $this->type,
            'unit' => $this->unit,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'validation_rules' => $this->validation_rules,
            'status' => $this->status,
            'options' => AttributeOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}

