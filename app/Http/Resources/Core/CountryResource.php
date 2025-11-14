<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CountryResource
 *
 * @author Abdul Wadood
 */
class CountryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
