<?php

namespace Database\Factories;

use App\Models\RelatedProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * RelatedProductFactory
 */
class RelatedProductFactory extends Factory
{
    protected $model = RelatedProduct::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'related_product_id' => \App\Models\Product::factory(),
        ];
    }
}
