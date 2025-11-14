<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CollectionProductResource
 *
 * @author Abdul Wadood
 */
class CollectionProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'collection_id' => $this->collection_id,
            'product_id' => $this->product_id,
            'sort' => $this->sort,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
