<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RelatedProductRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class RelatedProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'related_product_id' => ['required', 'integer', 'exists:products,id', 'different:product_id'],
        ];
    }
}
