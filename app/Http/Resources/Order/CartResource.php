<?php

namespace App\Http\Resources\Order;

use App\Services\Order\CartPricingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CartResource
 *
 * @author Abdul Wadood
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items');
        $itemCount = $items ? $items->sum('quantity') : 0;
        $pricing = app(CartPricingService::class)->compute($this->resource);

        return [
            'id' => $this->id,
            'cookie_key' => $this->cookie_key,
            'currency_code' => $this->currency_code,
            'shipping_method_code' => $this->shipping_method_code,
            'item_count' => $itemCount,
            'subtotal' => (float) ($pricing['items_subtotal'] ?? 0),
            'items_subtotal' => (float) ($pricing['items_subtotal'] ?? 0),
            'total_discount' => (float) ($pricing['total_discount'] ?? 0),
            'total_tax' => (float) ($pricing['total_tax'] ?? 0),
            'total_shipping' => (float) ($pricing['total_shipping'] ?? 0),
            'total' => (float) ($pricing['total'] ?? 0),
            'expires_at' => $this->expires_at,
            'checked_out_at' => $this->checked_out_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
