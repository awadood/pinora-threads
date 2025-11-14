<?php

namespace App\Http\Resources\Tax;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TaxRateResource
 *
 * @author Abdul Wadood
 */
class TaxRateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'amount' => $this->amount,
            'percentage' => $this->percentage,
            'refundable' => $this->refundable,
            'country_code' => $this->country_code,
            'state_code' => $this->state_code === null ? null : $this->state_code,
            'zipcode' => $this->zipcode,
            'zip_is_range' => $this->zip_is_range === null ? null : $this->zip_is_range,
            'zip_from' => $this->zip_from,
            'zip_to' => $this->zip_to,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
