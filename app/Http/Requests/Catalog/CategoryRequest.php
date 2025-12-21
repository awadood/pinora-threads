<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CategoryRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (Spatie permissions).
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'min:1',
                'max:255',
                'lowercase',
                'regex:/^(?!-)(?!.*--)[a-z0-9-]+(?<!-)$/',
                'unique:categories,slug,'.($this->category->id ?? 'NULL').',id',
            ],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort' => ['integer', 'min:0', 'max:65535'],
            'active' => ['boolean'],
        ];
    }
}
