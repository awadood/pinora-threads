<?php

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CollectionFactory
 */
class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'name' => ($n = fake()->unique()->randomElement(['Best Seller', 'On Sale', 'Today’s Top Pick'])),
            'slug' => fn (array $attrs) => strtolower(str_replace(' ', '-', $attrs['name'])),
            'sort' => 0,
            'notes' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }
}
