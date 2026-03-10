<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CustomerAddressResource
 *
 * @author Abdul Wadood
 */
class CustomerAddressResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultShippingAddressId = $request?->attributes?->get('default_shipping_address_id');
        $defaultBillingAddressId = $request?->attributes?->get('default_billing_address_id');

        $defaultShipping = $defaultShippingAddressId !== null
            ? (int) $this->id === (int) $defaultShippingAddressId
            : (bool) ($this->default_shipping ?? false);
        $defaultBilling = $defaultBillingAddressId !== null
            ? (int) $this->id === (int) $defaultBillingAddressId
            : (bool) ($this->default_billing ?? false);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'label' => $this->label,
            'name' => $this->name,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state_code' => $this->state_code,
            'postal_code' => $this->postal_code,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'default_shipping' => $defaultShipping,
            'default_billing' => $defaultBilling,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
