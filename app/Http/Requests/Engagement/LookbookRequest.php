<?php

namespace App\Http\Requests\Engagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * LookbookRequest
 *
 * Shared validation for creating/updating lookbooks.
 *
 * @author Abdul Wadood
 */
class LookbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lookbook = $this->route('lookbook');

        $slugRule = Rule::unique('lookbooks', 'slug');
        if ($lookbook) {
            $slugRule = $slugRule->ignore($lookbook->getKey());
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $slugRule],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'active' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
