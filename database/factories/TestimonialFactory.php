<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TestimonialFactory
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'author_name' => fake()->name(),
            'content' => fake()->sentences(3, true),
            'rating' => fake()->numberBetween(1, 5),
            'photo_url' => null,
            'sort_order' => 0,
            'published_at' => null,
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
