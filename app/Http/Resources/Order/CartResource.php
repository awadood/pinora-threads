<?php

namespace App\Http\Resources\Order;

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
        return [
            'id' => $this->id,
            'cookie_key' => $this->cookie_key,
            'currency_code' => $this->currency_code,
            'expires_at' => $this->expires_at,
            'checked_out_at' => $this->checked_out_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
