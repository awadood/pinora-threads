<?php

namespace Database\Factories;

use App\Models\PromotionCoupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * PromotionCouponFactory
 */
class PromotionCouponFactory extends Factory
{
    protected $model = PromotionCoupon::class;

    public function definition(): array
    {
        return [
            'promotion_id' => \App\Models\Promotion::factory(),
            'code' => strtoupper(fake()->unique()->bothify('SAVE####')),
            'usage_limit' => null,
            'usage_per_user' => null,
            'expiry' => null,
        ];
    }
}
