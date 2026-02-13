<?php

namespace App\Services\Order;

use App\Models\Cart;

/**
 * CartPricingService
 *
 * Computes cart totals using product prices in the cart currency.
 * Discounts/taxes/shipping are currently zero and will be expanded in later phases.
 */
final class CartPricingService
{
    public function __construct(
        protected ProductPriceResolver $prices,
        protected ShipmentRateService $shippingRates,
    ) {}

    public function computeItemsSubtotal(Cart $cart): float
    {
        $currencyCode = $cart->currency_code;

        $cart->loadMissing([
            'items.product.prices',
        ]);

        $itemsSubtotal = 0.00;

        foreach ($cart->items as $item) {
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $unitPrice = $this->prices->resolveForProduct($product, $currencyCode);
            $itemsSubtotal += $unitPrice * (int) $item->quantity;
        }

        return (float) $itemsSubtotal;
    }

    /**
     * @return array{
     *     currency_code: string,
     *     items_subtotal: float,
     *     total_discount: float,
     *     total_tax: float,
     *     total_shipping: float,
     *     total: float,
     * }
     */
    public function compute(Cart $cart): array
    {
        $currencyCode = $cart->currency_code;

        $itemsSubtotal = $this->computeItemsSubtotal($cart);
        $totalDiscount = 0.00;
        $totalTax = 0.00;
        $totalShipping = 0.00;

        if ($cart->shipping_method_code) {
            $shippingPrice = $this->shippingRates->resolveForMethod(
                $cart->shipping_method_code,
                $currencyCode,
                $itemsSubtotal
            );
            $totalShipping = (float) ($shippingPrice ?? 0.00);
        }

        $total = $itemsSubtotal - $totalDiscount + $totalTax + $totalShipping;

        return [
            'currency_code' => $currencyCode,
            'items_subtotal' => (float) $itemsSubtotal,
            'total_discount' => (float) $totalDiscount,
            'total_tax' => (float) $totalTax,
            'total_shipping' => (float) $totalShipping,
            'total' => (float) $total,
        ];
    }
}
