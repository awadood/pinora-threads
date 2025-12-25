<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CollectionRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class CollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:collections,slug,'.($this->collection->id ?? 'NULL').',id'],
            'sort' => ['integer', 'min:0', 'max:65535'],
            'notes' => ['nullable', 'string'],
            'active' => ['boolean'],
        ];
    }
}
