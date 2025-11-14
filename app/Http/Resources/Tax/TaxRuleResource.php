<?php

namespace App\Http\Resources\Tax;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TaxRuleResource
 *
 * @author Abdul Wadood
 */
class TaxRuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'priority' => $this->priority,
            'position' => $this->position,
            'calculate_subtotal' => $this->calculate_subtotal,
            'applies_to_shipping' => $this->applies_to_shipping,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
