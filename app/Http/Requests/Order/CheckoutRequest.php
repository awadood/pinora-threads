<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'email' => ['required', 'email'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'billing_address' => ['required_unless:shipping_method_code,pickup', 'array'],
            'billing_address.line1' => ['required_unless:shipping_method_code,pickup', 'string', 'max:255'],
            'billing_address.city' => ['required_unless:shipping_method_code,pickup', 'string', 'max:255'],
            'billing_address.state' => ['nullable', 'string', 'max:255'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address.country_code' => ['required_unless:shipping_method_code,pickup', 'string', 'size:2'],
            'shipping_address' => ['required_unless:shipping_method_code,pickup', 'array'],
            'shipping_address.line1' => ['required_unless:shipping_method_code,pickup', 'string', 'max:255'],
            'shipping_address.city' => ['required_unless:shipping_method_code,pickup', 'string', 'max:255'],
            'shipping_address.state' => ['nullable', 'string', 'max:255'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_address.country_code' => ['required_unless:shipping_method_code,pickup', 'string', 'size:2'],
            'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'shipping_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'shipping_method_code' => ['nullable', 'string', 'exists:shipment_methods,code'],
        ];
    }
}
