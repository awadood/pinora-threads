<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CategoryFactory
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => ($n = fake()->unique()->randomElement(['Unstitched', 'Stitched', 'Luxury', 'New Arrivals'])),
            'slug' => fn (array $attrs) => strtolower(str_replace(' ', '-', $attrs['name'])),
            'parent_id' => null,
            'sort' => 0,
            'active' => true,
        ];
    }
}
