<?php

namespace Database\Factories;

use App\Models\ProductBundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductBundleFactory
 */
class ProductBundleFactory extends Factory
{
    protected $model = ProductBundle::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'bundle_item_id' => \App\Models\Product::factory(),
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }
}
