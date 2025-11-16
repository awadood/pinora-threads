<?php

namespace App\Http\Requests\Engagement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TestimonialRequest
 *
 * Shared validation for creating/updating testimonials.
 *
 * @author Abdul Wadood
 */
class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware (auth + permissions) handles authorization.
        return true;
    }

    public function rules(): array
    {
        return [
            'author_name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,approved,rejected,archived'],
        ];
    }
}
