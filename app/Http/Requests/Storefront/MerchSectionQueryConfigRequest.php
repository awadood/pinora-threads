<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * MerchSectionQueryConfigRequest
 *
 * LOCKED:
 * - This request is intentionally similar to /api/products (storefront profile)
 * - BUT it disallows free-text q for merchandising
 * - filter[] is validated lightly here; normalization+allow-list enforcement is done by ProductFilters::parse()
 */
class MerchSectionQueryConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // q intentionally not allowed
            // 'q' => ['sometimes', 'string', 'max:200'],

            'sort' => [
                'sometimes',
                'string',
                Rule::in(['newest', 'name', '-name', 'price', '-price']),
            ],

            'filter' => ['sometimes', 'array'],
            // We do not attempt deep validation here because filter keys are dynamic
            // (attr.<code>.eq / attr.<code>.in). ProductFilters::parse() is the source of truth.
        ];
    }
}
