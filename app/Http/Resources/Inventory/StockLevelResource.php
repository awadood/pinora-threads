<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Catalog\VariantResource;
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
        // 1) Variant thumbnail (preferred)
        $variantAsset = optional($this->variant->thumbnailMedia)->asset;

        // 2) Product thumbnail (fallback)
        $productAsset = optional(optional($this->variant->product)->thumbnailMedia)->asset;

        $asset = $variantAsset ?: $productAsset;

        // 3) Prefer attachment override (and thumbnailMedia.alt_text). Fall back to asset alt text or product/variant title.
        $altText = $asset?->alt_text ?: trim(($variant->product->name ?? '').' '.($variant->title ?? '')) ?: null;

        return [
            'id' => $this->id,
            'stock_id' => $this->stock_id,
            'variant_id' => $this->variant_id,
            'quantity' => $this->quantity,
            'notify_below' => $this->notify_below,
            'allow_backorder' => $this->allow_backorder,
            'promised_at' => $this->promised_at,
            'restock_eta' => $this->restock_eta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'image' => $asset?->urlFor('thumb_sm'),
            'image_alt' => $altText,

            'stock' => StockResource::make($this->whenLoaded('stock')),

            'variant' => VariantResource::make($this->whenLoaded('variant')),
        ];
    }
}
