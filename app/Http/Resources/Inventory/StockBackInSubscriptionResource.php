<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Catalog\ProductResource;
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
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'email' => $this->email,
            'notified_at' => $this->notified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'product' => ProductResource::make($this->whenLoaded('product')),
        ];
    }
}
