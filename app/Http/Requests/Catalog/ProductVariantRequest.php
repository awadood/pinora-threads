<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductVariantRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class ProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku,'.($this->variant->id ?? 'NULL').',id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default' => ['boolean'],
            'active' => ['boolean'],
        ];
    }
}
