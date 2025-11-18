<?php

namespace App\Http\Requests\Shipping;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShipmentStatusRequest
 *
 * Used for updating shipment status (admin flow).
 *
 * @author Abdul Wadood
 */
class ShipmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipment_status_code' => [
                'required',
                'string',
                'exists:shipment_statuses,code',
            ],
        ];
    }
}
