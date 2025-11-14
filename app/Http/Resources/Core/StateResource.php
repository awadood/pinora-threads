<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * StateResource
 *
 * @author Abdul Wadood
 */
class StateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'country_code' => $this->country_code,
        ];
    }
}
