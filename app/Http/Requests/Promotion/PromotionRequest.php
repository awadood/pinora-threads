<?php

namespace App\Http\Requests\Promotion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PromotionRequest
 *
 * Shared request for creating/updating promotions.
 *
 * @author Abdul Wadood
 */
class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route permission middleware.
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['sometimes', 'string', 'max:255'],
            'from_date' => ['sometimes', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'applies_via' => ['sometimes', Rule::in(['auto', 'coupon'])],
            'usage_per_user' => ['nullable', 'integer', 'min:1'],
            'rules' => ['sometimes', 'array'], // you will define structure later
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['scheduled', 'ongoing', 'completed', 'paused'])],
        ];

        if ($this->isMethod('post')) {
            $rules['title'][] = 'required';
            $rules['from_date'][] = 'required';
            $rules['applies_via'][] = 'required';
            $rules['rules'][] = 'required';
            $rules['status'][] = 'required';
        }

        return $rules;
    }
}
