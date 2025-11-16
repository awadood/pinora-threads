<?php

namespace App\Http\Requests\Engagement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * LookbookItemRequest
 *
 * Validation for creating/updating lookbook items (styled looks).
 *
 * @author Abdul Wadood
 */
class LookbookItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['required', 'string', 'max:2048'],
            'notes' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
        ];
    }
}
