<?php

namespace App\Http\Resources\Tax;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TaxCalculationResource
 *
 * @author Abdul Wadood
 */
class TaxCalculationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tax_rate_id' => $this->tax_rate_id,
            'tax_rule_id' => $this->tax_rule_id,
            'user_tax_class_id' => $this->user_tax_class_id,
            'product_tax_class_id' => $this->product_tax_class_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
