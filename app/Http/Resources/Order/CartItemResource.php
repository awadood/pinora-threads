<?php

namespace App\Http\Resources\Order;

use App\Support\Media\MediaUrl;
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
        $product = $this->whenLoaded('product');
        $currencyCode = $this->cart?->currency_code;
        $price = $product?->prices?->firstWhere('currency_code', $currencyCode)
            ?? $product?->prices?->first();

        $thumbnailAsset = $product?->thumbnailMedia?->asset;
        $thumbnailUrl = MediaUrl::fromKeyOrUrl($thumbnailAsset?->urlFor('thumb_sm'));
        $fallbackUrls = array_values(array_unique(array_filter([
            MediaUrl::fromKeyOrUrl($thumbnailAsset?->urlFor('plp_480w')),
            MediaUrl::fromKeyOrUrl($thumbnailAsset?->urlFor(null)),
        ])));

        $unitPrice = (float) ($price?->amount ?? 0);
        $lineSubtotal = $unitPrice * (int) $this->quantity;
        $lineDiscount = 0.00;
        $lineTax = 0.00;
        $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'currency_code' => $price?->currency_code ?? $currencyCode,
            'unit_price' => $unitPrice,
            'compare_at' => $price?->compare_at !== null ? (float) $price->compare_at : null,
            'line_subtotal' => $lineSubtotal,
            'line_discount' => (float) $lineDiscount,
            'line_tax' => (float) $lineTax,
            'line_total' => (float) $lineTotal,
            'product' => $product ? [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'slug' => $product->slug,
                'thumbnail' => [
                    'url' => $thumbnailUrl,
                    'fallback_urls' => $fallbackUrls,
                ],
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
