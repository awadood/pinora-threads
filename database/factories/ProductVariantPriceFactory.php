<?php

namespace Database\Factories;

use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductVariantPriceFactory
 */
class ProductVariantPriceFactory extends Factory
{
    protected $model = ProductVariantPrice::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => \App\Models\ProductVariant::factory(),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'amount' => fake()->randomFloat(2, 10, 999),
            'compare_at' => fake()->optional()->randomFloat(2, 10, 1299),
        ];
    }
}
