<?php

namespace Database\Factories;

use App\Models\CategoryProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CategoryProductFactory
 */
class CategoryProductFactory extends Factory
{
    protected $model = CategoryProduct::class;

    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'product_id' => \App\Models\Product::factory(),
        ];
    }
}
