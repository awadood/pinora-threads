<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StockLevelRequest
 *
 * Validation for creating/updating stock levels per variant.
 *
 * @author Abdul Wadood
 */
class StockLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_id' => ['required', 'integer', 'exists:stocks,id'],
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'notify_below' => ['nullable', 'integer', 'min:0'],
            'allow_backorder' => ['boolean'],
            'promised_at' => ['nullable', 'date'],
            'restock_eta' => ['nullable', 'date'],
        ];
    }
}
