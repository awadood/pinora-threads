<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * AddressResource
 *
 * @author Abdul Wadood
 */
class AddressResource extends JsonResource
{
    public function toArray($request): array
    {
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
            'default_shipping' => $this->default_shipping,
            'default_billing' => $this->default_billing,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
