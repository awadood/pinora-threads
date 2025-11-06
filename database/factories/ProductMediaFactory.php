<?php

namespace Database\Factories;

use App\Models\ProductMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductMediaFactory
 */
class ProductMediaFactory extends Factory
{
    protected $model = ProductMedia::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'type' => fake()->randomElement(['image', 'video']),
            'url' => fake()->imageUrl(1200, 1200, 'fashion', true),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
