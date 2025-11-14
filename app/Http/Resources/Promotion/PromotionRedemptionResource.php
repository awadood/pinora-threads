<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PromotionRedemptionResource
 *
 * @author Abdul Wadood
 */
class PromotionRedemptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'promotion_id' => $this->promotion_id,
            'promotion_coupon_id' => $this->promotion_coupon_id,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'redeemed_at' => $this->redeemed_at,
            'currency_code' => $this->currency_code,
            'cart_amount' => $this->cart_amount,
            'discount_amount' => $this->discount_amount,
            'idempotency_key' => $this->idempotency_key,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
