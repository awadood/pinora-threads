<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class MerchSectionItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:0', 'max:200'],
            'items.*.item_id' => ['required', 'integer', 'min:1'],
            'items.*.position' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'items.*.active' => ['sometimes', 'boolean'],
        ];
    }
}
