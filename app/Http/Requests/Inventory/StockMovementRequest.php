<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StockMovementRequest
 *
 * Validation for stock movements.
 *
 * @author Abdul Wadood
 */
class StockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_id' => ['required', 'integer', 'exists:stocks,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'stock_movement_type_code' => ['required', 'string', 'exists:stock_movement_types,code'],
            'quantity_delta' => ['required', 'integer', 'not_in:0'],
            'stock_batch_id' => ['nullable', 'integer', 'exists:stock_batches,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'performed_by' => ['nullable', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
