<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductVariantPriceRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class ProductVariantPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_code' => ['required', 'string', 'size:3', 'exists:currencies,code'],
            'amount' => ['required', 'numeric', 'min:0'],
            'compare_at' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
