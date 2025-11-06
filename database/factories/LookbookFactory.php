<?php

namespace Database\Factories;

use App\Models\Lookbook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * LookbookFactory
 */
class LookbookFactory extends Factory
{
    protected $model = Lookbook::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'slug' => fn (array $attrs) => strtolower(str_replace(' ', '-', $attrs['title'])),
            'description' => fake()->optional()->paragraph(),
            'cover_image' => null,
            'active' => true,
            'sort_order' => 0,
            'published_at' => null,
        ];
    }
}
