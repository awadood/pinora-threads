<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PromotionResource
 *
 * @author Abdul Wadood
 */
class PromotionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'applies_via' => $this->applies_via,
            'usage_per_user' => $this->usage_per_user === null ? null : $this->usage_per_user,
            'rules' => $this->rules,
            'sort_order' => $this->sort_order,
            'active' => $this->active,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
