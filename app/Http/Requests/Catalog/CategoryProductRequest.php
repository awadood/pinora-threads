<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CategoryProductRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class CategoryProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
