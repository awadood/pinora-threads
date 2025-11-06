<?php

namespace Database\Factories;

use App\Models\StockBackInSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * StockBackInSubscriptionFactory
 */
class StockBackInSubscriptionFactory extends Factory
{
    protected $model = StockBackInSubscription::class;

    public function definition(): array
    {
        return [
            'variant_id' => \App\Models\ProductVariant::factory(),
            'user_id' => null,
            'email' => fake()->safeEmail(),
            'notified_at' => null,
        ];
    }
}
