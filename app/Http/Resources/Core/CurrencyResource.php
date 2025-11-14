<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CurrencyResource
 *
 * @author Abdul Wadood
 */
class CurrencyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
