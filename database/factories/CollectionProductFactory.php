<?php

namespace Database\Factories;

use App\Models\CollectionProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CollectionProductFactory
 */
class CollectionProductFactory extends Factory
{
    protected $model = CollectionProduct::class;

    public function definition(): array
    {
        return [
            'collection_id' => \App\Models\Collection::factory(),
            'product_id' => \App\Models\Product::factory(),
            'sort' => fake()->numberBetween(0, 10),
        ];
    }
}
