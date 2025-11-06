<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductFactory
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-#####')),
            'name' => fake()->words(3, true),
            'slug' => fn (array $attrs) => strtolower(str_replace(' ', '-', $attrs['name'])),
            'type' => fake()->randomElement(['simple', 'variable', 'bundle']),
            'tax_class_id' => \App\Models\TaxClass::factory(),
            'description' => fake()->optional()->paragraph(),
            'active' => true,
        ];
    }
}
