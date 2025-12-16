<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Catalog\VariantResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * StockMovementResource
 *
 * @author Abdul Wadood
 */
class StockMovementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'stock_id' => $this->stock_id,
            'variant_id' => $this->variant_id,
            'stock_movement_type_code' => $this->stock_movement_type_code,
            'quantity_delta' => $this->quantity_delta,
            'stock_batch_id' => $this->stock_batch_id,
            'order_id' => $this->order_id,
            'performed_by' => $this->performed_by,
            'reason' => $this->reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'stock' => StockResource::make($this->whenLoaded('stock')),

            'variant' => VariantResource::make($this->whenLoaded('variant')),
        ];
    }
}
