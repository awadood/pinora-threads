<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProductVariantMediaRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class ProductVariantMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:image,video'],
            'url' => ['required', 'string', 'max:2048'],
            'position' => ['integer', 'min:0', 'max:65535'],
        ];
    }
}
