<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductVariantFactory
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('VSKU-#####')),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'default' => false,
            'active' => true,
        ];
    }
}
