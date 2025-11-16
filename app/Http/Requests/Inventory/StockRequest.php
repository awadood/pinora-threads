<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StockRequest
 *
 * Validation for creating/updating stock locations.
 *
 * @author Abdul Wadood
 */
class StockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guarded via permissions middleware
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}
