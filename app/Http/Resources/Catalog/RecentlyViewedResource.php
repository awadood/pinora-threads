<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RecentlyViewedResource
 *
 * @author Abdul Wadood
 */
class RecentlyViewedResource extends JsonResource
{
    public function toArray($request): array
    {
        return array_merge([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
        ], $this->withTimestamps(['viewed_at' => 'viewed_at']));
    }
}
