<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * CheckoutRequest
 *
 * Validates payload for cart checkout.
 *
 * @author Abdul Wadood
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cart checkout is allowed for guests and authenticated users.
        return true;
    }

    public function rules(): array
    {
        $method = Str::lower($this->method());

        $rules = [
            'email' => ['required', 'email'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'billing_address' => ['required', 'array'],
            'billing_address.line1' => ['required', 'string', 'max:255'],
            'billing_address.city' => ['required', 'string', 'max:255'],
            'billing_address.country_code' => ['required', 'string', 'size:2'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.line1' => ['required', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:255'],
            'shipping_address.country_code' => ['required', 'string', 'size:2'],
            'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'shipping_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
        ];

        return $rules;
    }
}
