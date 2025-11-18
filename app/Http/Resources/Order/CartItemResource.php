<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CartItemResource
 *
 * @author Abdul Wadood
 */
class CartItemResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->whenLoaded('productVariant');
        $product = $variant?->product;

        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'product_variant_id' => $this->product_variant_id,
            'quantity' => $this->quantity,
            'variant' => $variant ? [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'title' => $variant->title,
            ] : null,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
