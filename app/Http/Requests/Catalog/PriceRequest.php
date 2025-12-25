<?php

namespace App\Http\Requests\Catalog;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * PriceRequest
 *
 * Validates payload for:
 * PUT /products/{product}/pricing
 *
 * Payload:
 * - product_prices[]: currency_code, amount, compare_at
 * - variant_prices[]: product_variant_id, prices[](...)
 *
 * Notes:
 * - Missing currency objects mean "missing price" (do not send blank rows).
 * - compare_at must be null or >= amount.
 */
class PriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        $productId = $product?->id;

        return [
            'product_prices' => ['sometimes', 'array'],
            'product_prices.*.currency_code' => [
                'required',
                'string',
                'size:3',
                Rule::exists('currencies', 'code'),
            ],
            'product_prices.*.amount' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'product_prices.*.compare_at' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'variant_prices' => ['sometimes', 'array'],
            'variant_prices.*.product_variant_id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id')->where(function ($q) use ($productId) {
                    // Variant must belong to the product from route model binding
                    if ($productId !== null) {
                        $q->where('product_id', $productId);
                    }
                }),
            ],
            'variant_prices.*.prices' => ['required', 'array', 'min:1'],
            'variant_prices.*.prices.*.currency_code' => [
                'required',
                'string',
                'size:3',
                Rule::exists('currencies', 'code'),
            ],
            'variant_prices.*.prices.*.amount' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'variant_prices.*.prices.*.compare_at' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // product_prices compare_at >= amount
            $productPrices = $this->input('product_prices', []);
            if (is_array($productPrices)) {
                foreach ($productPrices as $i => $p) {
                    $amount = $p['amount'] ?? null;
                    $compare = $p['compare_at'] ?? null;
                    if ($compare !== null && $amount !== null && (float) $compare < (float) $amount) {
                        $v->errors()->add("product_prices.$i.compare_at", 'compare_at must be greater than or equal to amount.');
                    }
                }
            }

            // variant_prices[*].prices[*] compare_at >= amount
            $variantPrices = $this->input('variant_prices', []);
            if (is_array($variantPrices)) {
                foreach ($variantPrices as $i => $row) {
                    $prices = $row['prices'] ?? [];
                    if (! is_array($prices)) {
                        continue;
                    }

                    foreach ($prices as $j => $p) {
                        $amount = $p['amount'] ?? null;
                        $compare = $p['compare_at'] ?? null;
                        if ($compare !== null && $amount !== null && (float) $compare < (float) $amount) {
                            $v->errors()->add("variant_prices.$i.prices.$j.compare_at", 'compare_at must be greater than or equal to amount.');
                        }
                    }
                }
            }
        });
    }
}
