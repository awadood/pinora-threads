<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * PriceRequest
 *
 * Validates payload for:
 * PUT /products/{product}/prices
 *
 * Payload:
 * - product_prices[]: currency_code, amount, compare_at
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

        });
    }
}
