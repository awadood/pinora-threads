<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * StockBackInSubscriptionResource
 *
 * @author Abdul Wadood
 */
class StockBackInSubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'variant_id' => $this->variant_id,
            'user_id' => $this->user_id,
            'email' => $this->email,
            'notified_at' => optional($this->notified_at)->toDateTimeString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
