<?php

namespace App\Services\Tax;

use App\Models\TaxRate;
use App\Repositories\Tax\Contracts\ITaxCalculationRepository;
use Illuminate\Support\Collection;

/**
 * TaxCalculatorService
 *
 * Core tax engine implementing Pinnora Tax Engine Blueprint v1.
 *
 * Responsibilities:
 *  - Given customer + product tax classes and destination (US/PK),
 *    compute item-level and shipping-level taxes.
 *  - For US: exclusive tax, destination-based, driven by shipping address.
 *  - For PK: typically inclusive tax (handled upstream; here we focus on US).
 *
 * This service is intentionally framework-agnostic at the boundary:
 * it accepts simple arrays and returns structured arrays that can be
 * used to populate Order totals and snapshots (orders.taxes JSON).
 *
 * @author Abdul Wadood
 */
class TaxCalculatorService
{
    public function __construct(protected ITaxCalculationRepository $taxCalculationRepository) {}

    /**
     * Calculate taxes for a draft order/cart.
     *
     * @param  array<string,mixed>  $context
     *                                        [
     *                                        'user_tax_class_id' => int,
     *                                        'shipping_address' => [
     *                                        'country_code' => string,
     *                                        'state_code'   => ?string,
     *                                        'zipcode'      => string,
     *                                        ],
     *                                        'currency_code'   => string,
     *                                        'tax_inclusive'   => bool,
     *                                        'items'           => array<int,array{
     *                                        product_id: int,
     *                                        product_tax_class_id: int,
     *                                        quantity: int,
     *                                        unit_price: string|float,
     *                                        line_discount: string|float
     *                                        }>,
     *                                        'shipping'        => array{
     *                                        amount: string|float,
     *                                        shipping_tax_class_id: ?int
     *                                        }
     *                                        ]
     * @return array<string,mixed>
     *                             [
     *                             'items' => [
     *                             [
     *                             'product_id' => ...,
     *                             'tax' => '12.34',
     *                             'breakdown' => [
     *                             [
     *                             'tax_rate_id' => int,
     *                             'tax_rule_id' => int,
     *                             'rule_code' => string,
     *                             'rate_code' => string,
     *                             'amount' => '4.56',
     *                             ],
     *                             ],
     *                             ],
     *                             ...
     *                             ],
     *                             'shipping' => [
     *                             'tax' => '3.21',
     *                             'breakdown' => [...]
     *                             ],
     *                             'totals' => [
     *                             'items_tax' => 'xx.xx',
     *                             'shipping_tax' => 'yy.yy',
     *                             'total_tax' => 'zz.zz',
     *                             ],
     *                             'snapshot' => [
     *                             // overall JSON snapshot suitable for orders.taxes column
     *                             ]
     *                             ]
     */
    public function calculate(array $context): array
    {
        $userTaxClassId = (int) ($context['user_tax_class_id'] ?? 0);
        $shipping = $context['shipping_address'] ?? [];
        $countryCode = (string) ($shipping['country_code'] ?? '');
        $stateCode = $shipping['state_code'] ?? null;
        $zipcode = (string) ($shipping['zipcode'] ?? '');
        $items = $context['items'] ?? [];
        $shippingInfo = $context['shipping'] ?? ['amount' => 0, 'shipping_tax_class_id' => null];

        $itemsResult = [];
        $itemsTaxTotal = 0.0;

        foreach ($items as $item) {
            $line = $this->calculateLineTax(
                $userTaxClassId,
                (int) $item['product_tax_class_id'],
                $countryCode,
                $stateCode,
                $zipcode,
                (float) $item['unit_price'],
                (int) $item['quantity'],
                (float) ($item['line_discount'] ?? 0.0)
            );

            $itemsTaxTotal += $line['tax'];

            $itemsResult[] = [
                'product_id' => (int) $item['product_id'],
                'tax' => number_format($line['tax'], 2, '.', ''),
                'breakdown' => $line['breakdown'],
            ];
        }

        $shippingAmount = (float) ($shippingInfo['amount'] ?? 0.0);
        $shippingTaxClassId = $shippingInfo['shipping_tax_class_id'] ?? null;

        $shippingResult = [
            'tax' => '0.00',
            'breakdown' => [],
        ];
        $shippingTaxTotal = 0.0;

        if ($shippingAmount > 0 && $shippingTaxClassId !== null) {
            $shippingLine = $this->calculateShippingTax(
                $userTaxClassId,
                (int) $shippingTaxClassId,
                $countryCode,
                $stateCode,
                $zipcode,
                $shippingAmount
            );
            $shippingTaxTotal = $shippingLine['tax'];
            $shippingResult = [
                'tax' => number_format($shippingTaxTotal, 2, '.', ''),
                'breakdown' => $shippingLine['breakdown'],
            ];
        }

        $totalTax = $itemsTaxTotal + $shippingTaxTotal;

        $snapshot = [
            'shipping_address' => $shipping,
            'items' => $itemsResult,
            'shipping' => $shippingResult,
            'totals' => [
                'items_tax' => number_format($itemsTaxTotal, 2, '.', ''),
                'shipping_tax' => number_format($shippingTaxTotal, 2, '.', ''),
                'total_tax' => number_format($totalTax, 2, '.', ''),
            ],
        ];

        return [
            'items' => $itemsResult,
            'shipping' => $shippingResult,
            'totals' => $snapshot['totals'],
            'snapshot' => $snapshot,
        ];
    }

    /**
     * Calculate tax for a single line item.
     *
     * @return array{tax: float, breakdown: array<int,array<string,mixed>>}
     */
    protected function calculateLineTax(
        int $userTaxClassId,
        int $productTaxClassId,
        string $countryCode,
        ?string $stateCode,
        string $zipcode,
        float $unitPrice,
        int $quantity,
        float $lineDiscount
    ): array {
        $calculations = $this->taxCalculationRepository->findApplicable(
            $userTaxClassId,
            $productTaxClassId,
            $countryCode,
            $stateCode,
            $zipcode
        );

        if ($calculations->isEmpty()) {
            return [
                'tax' => 0.0,
                'breakdown' => [],
            ];
        }

        /** @var Collection<int,\App\Models\TaxCalculation> $calculations */

        // Group by rule priority and find the lowest one.
        $grouped = $calculations->groupBy(fn ($calc) => $calc->taxRule->priority);
        $minPriority = min(array_keys($grouped->toArray()));
        $applicable = $grouped[$minPriority];

        $taxBase = max(0.0, ($unitPrice * $quantity) - $lineDiscount);

        $totalTax = 0.0;
        $breakdown = [];

        /** @var \App\Models\TaxCalculation $calc */
        foreach ($applicable->sortBy('taxRule.position') as $calc) {
            $rule = $calc->taxRule;
            $rate = $calc->taxRate;

            if (! $rule->calculate_subtotal) {
                // if we ever need base-before-discount, adjust here
            }

            $amount = $this->computeTaxAmount($rate, $taxBase);

            if ($amount <= 0.0) {
                continue;
            }

            $totalTax += $amount;

            $breakdown[] = [
                'tax_rate_id' => $rate->id,
                'tax_rule_id' => $rule->id,
                'rule_code' => $rule->code,
                'rate_code' => $rate->code,
                'amount' => number_format($amount, 2, '.', ''),
            ];
        }

        return [
            'tax' => $totalTax,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate tax for shipping amount using shipping tax class.
     *
     * @return array{tax: float, breakdown: array<int,array<string,mixed>>}
     */
    protected function calculateShippingTax(
        int $userTaxClassId,
        int $shippingTaxClassId,
        string $countryCode,
        ?string $stateCode,
        string $zipcode,
        float $shippingAmount
    ): array {
        $calculations = $this->taxCalculationRepository->findApplicable(
            $userTaxClassId,
            $shippingTaxClassId,
            $countryCode,
            $stateCode,
            $zipcode
        );

        if ($calculations->isEmpty()) {
            return [
                'tax' => 0.0,
                'breakdown' => [],
            ];
        }

        $grouped = $calculations->filter(function ($calc) {
            return $calc->taxRule->applies_to_shipping;
        })->groupBy(fn ($calc) => $calc->taxRule->priority);

        if ($grouped->isEmpty()) {
            return [
                'tax' => 0.0,
                'breakdown' => [],
            ];
        }

        $minPriority = min(array_keys($grouped->toArray()));
        $applicable = $grouped[$minPriority];

        $taxBase = max(0.0, $shippingAmount);

        $totalTax = 0.0;
        $breakdown = [];

        foreach ($applicable->sortBy('taxRule.position') as $calc) {
            $rule = $calc->taxRule;
            $rate = $calc->taxRate;

            $amount = $this->computeTaxAmount($rate, $taxBase);

            if ($amount <= 0.0) {
                continue;
            }

            $totalTax += $amount;

            $breakdown[] = [
                'tax_rate_id' => $rate->id,
                'tax_rule_id' => $rule->id,
                'rule_code' => $rule->code,
                'rate_code' => $rate->code,
                'amount' => number_format($amount, 2, '.', ''),
            ];
        }

        return [
            'tax' => $totalTax,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Compute tax amount for a given rate and base.
     */
    protected function computeTaxAmount(TaxRate $rate, float $base): float
    {
        if ($base <= 0.0) {
            return 0.0;
        }

        if ($rate->percentage) {
            return round($base * ((float) $rate->amount / 100.0), 2);
        }

        // Flat amount – applied as fee per line.
        return round((float) $rate->amount, 2);
    }
}
