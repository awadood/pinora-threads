<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * StockLevelResource
 *
 * @author Abdul Wadood
 */
class StockLevelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'stock_id' => $this->stock_id,
            'variant_id' => $this->variant_id,
            'quantity' => $this->quantity,
            'notify_below' => $this->notify_below,
            'allow_backorder' => $this->allow_backorder,
            'promised_at' => optional($this->promised_at)->toDateTimeString(),
            'restock_eta' => optional($this->restock_eta)->toDateTimeString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'stock' => StockResource::make($this->whenLoaded('stock')),
        ];
    }
}
