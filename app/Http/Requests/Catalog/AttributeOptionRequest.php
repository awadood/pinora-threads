<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AttributeOptionRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class AttributeOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'value' => ['required', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
