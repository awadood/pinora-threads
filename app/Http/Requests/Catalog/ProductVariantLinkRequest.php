<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductVariantLinkRequest
 *
 * Validates payloads for linking variant products to a parent product.
 *
 * @author Abdul Wadood
 */
class ProductVariantLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
