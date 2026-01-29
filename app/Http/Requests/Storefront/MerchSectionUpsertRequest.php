<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * MerchSectionUpsertRequest
 *
 * Rules:
 * - code is stable; treat like an identifier
 * - sections are homogeneous (item_type)
 * - scheduling optional but supported
 * - mode curated/query supported from day 1
 */
class MerchSectionUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Guard with your admin middleware/permissions as per your admin API.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:160'],
            'surface' => ['sometimes', 'string', 'max:50'],

            'item_type' => ['required', Rule::in(['product', 'collection', 'category'])],
            'mode' => ['required', Rule::in(['curated', 'query'])],

            'default_limit' => ['sometimes', 'integer', 'min:1', 'max:50'],

            'country_code' => ['nullable', 'string', 'size:3', 'exists:countries,code'],

            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],

            'sort' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'active' => ['sometimes', 'boolean'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
