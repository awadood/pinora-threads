<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * VariantAttributeResource
 *
 * @author Abdul Wadood
 */
class VariantAttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_variant_id' => $this->product_variant_id,
            'attribute_id' => $this->attribute_id,
            'option_id' => $this->option_id === null ? null : $this->option_id,
            'value' => $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'attribute' => $this->whenLoaded('attribute'),

            'option' => $this->whenLoaded('option'),
        ];
    }
}
