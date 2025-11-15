<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductPriceRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class ProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
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
