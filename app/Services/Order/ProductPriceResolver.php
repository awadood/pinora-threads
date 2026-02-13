<?php

namespace App\Services\Order;

use App\Models\Product;
use App\Models\ProductPrice;
use RuntimeException;

/**
 * ProductPriceResolver
 *
 * Resolves unit prices for a product in a given currency.
 */
final class ProductPriceResolver
{
    public function resolve(int $productId, string $currencyCode): float
    {
        /** @var ProductPrice|null $pp */
        $pp = ProductPrice::where('product_id', $productId)
            ->where('currency_code', $currencyCode)
            ->first();

        if ($pp) {
            return (float) $pp->amount;
        }

        throw new RuntimeException('Price not configured for this product in currency '.$currencyCode);
    }

    public function resolveForProduct(Product $product, string $currencyCode): float
    {
        if ($product->relationLoaded('prices')) {
            $price = $product->prices->firstWhere('currency_code', $currencyCode)
                ?? $product->prices->first();

            if ($price) {
                return (float) $price->amount;
            }
        }

        return $this->resolve($product->id, $currencyCode);
    }
}
