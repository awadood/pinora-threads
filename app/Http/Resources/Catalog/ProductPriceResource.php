<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProductPriceResource
 *
 * @author Abdul Wadood
 */
class ProductPriceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_id' => $this->product_id,
            'currency_code' => $this->currency_code,
            'amount' => (float) $this->amount,
            'compare_at' => (float) $this->compare_at === null ? null : $this->compare_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
