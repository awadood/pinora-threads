<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Catalog\ProductResource;
use App\Support\Media\MediaUrl;
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
        $product = $this->whenLoaded('product');
        $asset = optional($product?->thumbnailMedia)->asset;
        $altText = $asset?->alt_text ?: ($product?->name ?? null);

        return [
            'id' => $this->id,
            'stock_id' => $this->stock_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'notify_below' => $this->notify_below,
            'allow_backorder' => $this->allow_backorder,
            'promised_at' => $this->promised_at,
            'restock_eta' => $this->restock_eta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'image' => MediaUrl::fromKeyOrUrl($asset?->urlFor('thumb_sm')),
            'image_alt' => $altText,

            'stock' => StockResource::make($this->whenLoaded('stock')),

            'product' => ProductResource::make($product),
        ];
    }
}
