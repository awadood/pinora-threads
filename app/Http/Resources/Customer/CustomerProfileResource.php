<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CustomerProfileResource
 *
 * @author Abdul Wadood
 */
class CustomerProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tax_class_id' => $this->tax_class_id,
            'marketing_email_opt_in' => $this->marketing_email_opt_in,
            'marketing_sms_opt_in' => $this->marketing_sms_opt_in,
            'preferred_currency' => $this->preferred_currency,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
