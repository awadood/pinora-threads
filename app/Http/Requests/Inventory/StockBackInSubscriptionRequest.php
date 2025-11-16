<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StockBackInSubscriptionRequest
 *
 * Validation for back-in-stock subscription creation.
 *
 * @author Abdul Wadood
 */
class StockBackInSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'email' => ['nullable', 'email'],
        ];
    }
}
