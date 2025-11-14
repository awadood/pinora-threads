<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PromotionCouponResource
 *
 * @author Abdul Wadood
 */
class PromotionCouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'promotion_id' => $this->promotion_id,
            'code' => $this->code,
            'usage_limit' => $this->usage_limit === null ? null : $this->usage_limit,
            'usage_per_user' => $this->usage_per_user === null ? null : $this->usage_per_user,
            'expiry' => $this->expiry,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
