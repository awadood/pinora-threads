<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Catalog\VariantResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * StockBatchResource
 *
 * @author Abdul Wadood
 */
class StockBatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'stock_id' => $this->stock_id,
            'variant_id' => $this->variant_id,
            'received_at' => $this->received_at,
            'currency_code' => $this->currency_code,
            'unit_cost' => $this->unit_cost,
            'qty_received' => $this->qty_received,
            'qty_remaining' => $this->qty_remaining,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'stock' => StockResource::make($this->whenLoaded('stock')),

            'variant' => VariantResource::make($this->whenLoaded('variant')),
        ];
    }
}
