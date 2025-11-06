<?php

namespace Database\Factories;

use App\Models\PromotionRedemption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * PromotionRedemptionFactory
 */
class PromotionRedemptionFactory extends Factory
{
    protected $model = PromotionRedemption::class;

    public function definition(): array
    {
        return [
            'promotion_id' => \App\Models\Promotion::factory(),
            'promotion_coupon_id' => null,
            'user_id' => null,
            'order_id' => null,
            'redeemed_at' => now(),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'cart_amount' => fake()->randomFloat(2, 10, 400),
            'discount_amount' => fake()->randomFloat(2, 1, 50),
            'idempotency_key' => strtoupper(fake()->unique()->bothify('RID#######')),
        ];
    }
}
