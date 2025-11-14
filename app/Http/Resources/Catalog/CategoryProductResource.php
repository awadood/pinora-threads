<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CategoryProductResource
 *
 * @author Abdul Wadood
 */
class CategoryProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'category_id' => $this->category_id,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
