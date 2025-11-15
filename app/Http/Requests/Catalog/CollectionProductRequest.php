<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CollectionProductRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class CollectionProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'collection_id' => ['required', 'integer', 'exists:collections,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'sort' => ['integer', 'min:0', 'max:65535'],
        ];
    }
}
