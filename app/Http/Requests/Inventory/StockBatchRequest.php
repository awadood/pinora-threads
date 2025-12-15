<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StockBatchRequest
 *
 * Validation for costed stock batches.
 *
 * @author Abdul Wadood
 */
class StockBatchRequest extends FormRequest
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
            'received_at' => ['required', 'date'],
            'currency_code' => ['required', 'string', 'size:3', 'exists:currencies,code'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'qty_received' => ['required', 'integer', 'min:1'],
        ];
    }
}
