<?php

namespace App\Http\Requests\Shipping;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * ShipmentRequest
 *
 * Used for both creating and updating shipments.
 * POST  => required fields
 * PATCH => all fields optional (for partial update).
 *
 * @author Abdul Wadood
 */
class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Actual permission checks are done via route middleware (Spatie).
        return true;
    }

    public function rules(): array
    {
        $isPost = Str::lower($this->method()) === 'post';

        return [
            'stock_id' => [$isPost ? 'required' : 'sometimes', 'integer', 'exists:stocks,id'],
            'shipment_method_code' => [$isPost ? 'required' : 'sometimes', 'string', 'exists:shipment_methods,code'],
            // Status at creation is optional; usually default comes from db / service.
            'shipment_status_code' => ['sometimes', 'string', 'exists:shipment_statuses,code'],
            'carrier' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tracking_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tracking_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'shipping_cost' => [$isPost ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'shipping_tax' => [$isPost ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'label_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'carrier_payload' => ['sometimes', 'nullable', 'array'],
            'notes' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
