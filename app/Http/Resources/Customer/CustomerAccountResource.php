<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CustomerAccountResource
 *
 * @author Abdul Wadood
 */
class CustomerAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'marketing_email_opt_in' => $this->marketing_email_opt_in,
            'marketing_email_consented_at' => $this->marketing_email_consented_at,
            'marketing_email_revoked_at' => $this->marketing_email_revoked_at,
            'marketing_email_consent_ip' => $this->marketing_email_consent_ip,
            'marketing_email_consent_source' => $this->marketing_email_consent_source,
            'marketing_sms_opt_in' => $this->marketing_sms_opt_in,
            'marketing_sms_consented_at' => $this->marketing_sms_consented_at,
            'marketing_sms_revoked_at' => $this->marketing_sms_revoked_at,
            'marketing_sms_consent_ip' => $this->marketing_sms_consent_ip,
            'marketing_sms_consent_source' => $this->marketing_sms_consent_source,
            'preferred_currency' => $this->preferred_currency,
            'default_shipping_address_id' => $this->default_shipping_address_id,
            'default_billing_address_id' => $this->default_billing_address_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
