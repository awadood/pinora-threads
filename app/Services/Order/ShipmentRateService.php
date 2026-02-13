<?php

namespace App\Services\Order;

use App\Models\Cart;
use App\Models\ShipmentMethod;
use App\Models\ShipmentRate;

/**
 * ShipmentRateService
 *
 * Determines available shipping methods and rates for a cart.
 */
final class ShipmentRateService
{
    public function __construct(protected ProductPriceResolver $prices) {}

    /**
     * @return array<int, array<string,mixed>>
     */
    public function listForCart(Cart $cart): array
    {
        $itemsSubtotal = $this->computeItemsSubtotal($cart);
        $currencyCode = $cart->currency_code;

        $methods = ShipmentMethod::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        if ($methods->isEmpty()) {
            return [];
        }

        $rates = ShipmentRate::query()
            ->where('active', true)
            ->where('currency_code', $currencyCode)
            ->get()
            ->groupBy('shipment_method_code');

        $pickup = null;
        $deliveryCandidates = [];

        foreach ($methods as $method) {
            $price = $this->resolveForMethod($method->code, $currencyCode, $itemsSubtotal, $rates->get($method->code));

            if ($price === null) {
                continue;
            }

            if ($method->code === ShipmentMethod::PICKUP) {
                $pickup = [
                    'code' => $method->code,
                    'name' => 'Pickup',
                    'type' => 'pickup',
                    'currency_code' => $currencyCode,
                    'price' => $price,
                    'sort_order' => $method->sort_order,
                ];

                continue;
            }

            $deliveryCandidates[] = [
                'code' => $method->code,
                'name' => 'Home Delivery',
                'type' => 'delivery',
                'currency_code' => $currencyCode,
                'price' => $price,
                'sort_order' => $method->sort_order,
            ];
        }

        $delivery = $this->pickBestDelivery($deliveryCandidates);

        $list = array_values(array_filter([$pickup, $delivery]));

        $hasSelected = $cart->shipping_method_code !== null;
        $defaultCode = null;

        if (! $hasSelected) {
            $defaultCode = $delivery['code'] ?? $pickup['code'] ?? null;
        }

        foreach ($list as &$item) {
            $item['is_selected'] = $cart->shipping_method_code === $item['code'];
            $item['is_default'] = ! $hasSelected && $defaultCode === $item['code'];
        }
        unset($item);

        return $list;
    }

    public function resolveSelectedForCart(Cart $cart): ?array
    {
        if (! $cart->shipping_method_code) {
            return null;
        }

        $itemsSubtotal = $this->computeItemsSubtotal($cart);
        $currencyCode = $cart->currency_code;

        $price = $this->resolveForMethod($cart->shipping_method_code, $currencyCode, $itemsSubtotal);

        if ($price === null) {
            return null;
        }

        /** @var ShipmentMethod|null $method */
        $method = ShipmentMethod::find($cart->shipping_method_code);
        if (! $method || ! $method->active) {
            return null;
        }

        return [
            'code' => $method->code,
            'name' => $method->name,
            'currency_code' => $currencyCode,
            'price' => $price,
        ];
    }

    /**
     * @param  iterable<int, ShipmentRate>|null  $rates
     */
    public function resolveForMethod(
        string $methodCode,
        string $currencyCode,
        float $itemsSubtotal,
        ?iterable $rates = null,
    ): ?float {
        $candidates = $rates;

        if ($candidates === null) {
            $candidates = ShipmentRate::query()
                ->where('active', true)
                ->where('currency_code', $currencyCode)
                ->where('shipment_method_code', $methodCode)
                ->get();
        }

        $matched = [];

        foreach ($candidates as $rate) {
            $min = $rate->min_subtotal ?? 0.0;
            $max = $rate->max_subtotal;

            if ($itemsSubtotal < $min) {
                continue;
            }

            if ($max !== null && $itemsSubtotal > $max) {
                continue;
            }

            $matched[] = $rate;
        }

        if (empty($matched)) {
            return null;
        }

        usort($matched, function ($a, $b) {
            $aMin = $a->min_subtotal ?? 0.0;
            $bMin = $b->min_subtotal ?? 0.0;

            if ($aMin === $bMin) {
                return ($a->sort_order ?? 0) <=> ($b->sort_order ?? 0);
            }

            return $bMin <=> $aMin; // prefer highest min_subtotal
        });

        return (float) $matched[0]->price;
    }

    protected function computeItemsSubtotal(Cart $cart): float
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
     * @param  array<int, array<string,mixed>>  $candidates
     */
    protected function pickBestDelivery(array $candidates): ?array
    {
        if (empty($candidates)) {
            return null;
        }

        foreach ($candidates as $candidate) {
            if (($candidate['code'] ?? null) === ShipmentMethod::SELF) {
                return $candidate;
            }
        }

        usort($candidates, function ($a, $b) {
            $priceCmp = ($a['price'] ?? 0) <=> ($b['price'] ?? 0);
            if ($priceCmp !== 0) {
                return $priceCmp;
            }

            return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
        });

        return $candidates[0];
    }
}
