<?php

namespace App\Http\Resources\Order;

use App\Support\Media\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OrderItemResource
 *
 * @author Abdul Wadood
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        $productSnapshot = $this->normalizeProductSnapshot($this->product);

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'sku' => $this->sku,
            'product' => $productSnapshot,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Keep stored snapshot intact while exposing public media URLs in API.
     */
    private function normalizeProductSnapshot(mixed $snapshot): mixed
    {
        if (! is_array($snapshot)) {
            return $snapshot;
        }

        $media = $snapshot['media'] ?? null;
        if (! is_array($media)) {
            return $snapshot;
        }

        foreach (['thumbnail', 'hero', 'og_image'] as $key) {
            $media[$key] = MediaUrl::fromKeyOrUrl($media[$key] ?? null);
        }

        $snapshot['media'] = $media;

        return $snapshot;
    }
}
