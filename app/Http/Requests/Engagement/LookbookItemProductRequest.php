<?php

namespace App\Http\Requests\Engagement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * LookbookItemProductRequest
 *
 * Validation for attaching products/variants to lookbook items.
 *
 * @author Abdul Wadood
 */
class LookbookItemProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
        ];
    }
}
