<?php

namespace App\Http\Requests\Promotion;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PromotionCouponRequest
 *
 * Shared request for creating/updating promotion coupons.
 *
 * @author Abdul Wadood
 */
class PromotionCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'code' => ['sometimes', 'string', 'max:255'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_user' => ['nullable', 'integer', 'min:1'],
            'expiry' => ['nullable', 'date'],
        ];

        if ($this->isMethod('post')) {
            $rules['code'][] = 'required';
        }

        return $rules;
    }
}
