<?php

namespace Database\Factories;

use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductPriceFactory
 */
class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'amount' => fake()->randomFloat(2, 10, 999),
            'compare_at' => fake()->optional()->randomFloat(2, 10, 1299),
        ];
    }
}
