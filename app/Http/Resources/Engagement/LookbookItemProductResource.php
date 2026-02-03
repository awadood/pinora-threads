<?php

namespace App\Http\Resources\Engagement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * LookbookItemProductResource
 *
 * @author Abdul Wadood
 */
class LookbookItemProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lookbook_item_id' => $this->lookbook_item_id,
            'product_id' => $this->product_id,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
