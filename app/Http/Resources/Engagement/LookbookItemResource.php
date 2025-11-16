<?php

namespace App\Http\Resources\Engagement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * LookbookItemResource
 *
 * @author Abdul Wadood
 */
class LookbookItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lookbook_id' => $this->lookbook_id,
            'title' => $this->title,
            'image_url' => $this->image_url,
            'notes' => $this->notes,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
