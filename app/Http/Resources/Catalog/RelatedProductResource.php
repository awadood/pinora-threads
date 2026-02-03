<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RelatedProductResource
 *
 * @author Abdul Wadood
 */
class RelatedProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $pivot = $this->pivot ?? null;

        return [
            'product_id' => $pivot?->product_id ?? $this->product_id,
            'related_product_id' => $pivot?->related_product_id ?? $this->related_product_id,
            'created_at' => $pivot?->created_at ?? $this->created_at,
            'updated_at' => $pivot?->updated_at ?? $this->updated_at,
        ];
    }
}
