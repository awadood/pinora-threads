<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AttributeRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class AttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'unique:attributes,code'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:text,select'],
            'active' => ['boolean'],
        ];
    }
}
