<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * PromotionFactory
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'from_date' => now(),
            'to_date' => null,
            'applies_via' => fake()->randomElement(['auto', 'coupon']),
            'usage_per_user' => null,
            'rules' => json_encode([]),
            'sort_order' => 0,
            'active' => true,
            'status' => fake()->randomElement(['scheduled', 'ongoing', 'paused']),
        ];
    }
}
