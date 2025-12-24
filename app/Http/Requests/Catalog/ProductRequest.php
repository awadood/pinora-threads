<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku,'.($this->product->id ?? 'NULL').',id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug,'.($this->product->id ?? 'NULL').',id'],
            'type' => ['required', 'string', 'in:simple,variable,bundle'],
            'description' => ['nullable', 'string'],
            'tax_class_id' => ['required', 'integer', 'exists:tax_classes,id'],
        ];
    }
}
