<?php

namespace Database\Factories;

use App\Models\ProductVariantMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductVariantMediaFactory
 */
class ProductVariantMediaFactory extends Factory
{
    protected $model = ProductVariantMedia::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => \App\Models\ProductVariant::factory(),
            'type' => fake()->randomElement(['image', 'video']),
            'url' => fake()->imageUrl(1200, 1200, 'fashion', true),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
