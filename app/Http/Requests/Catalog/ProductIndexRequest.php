<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:120'],

            // q is free text
            'q' => ['sometimes', 'string', 'max:200'],

            // sorting
            'sort' => [
                'sometimes',
                'string',
                Rule::in(['newest', 'name', '-name', 'price', '-price']),
            ],

            // filters[] is an associative array
            'filter' => ['sometimes', 'array'],
        ];
    }
}
