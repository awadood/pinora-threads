<?php

namespace Database\Factories;

use App\Models\AttributeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * AttributeOptionFactory
 */
class AttributeOptionFactory extends Factory
{
    protected $model = AttributeOption::class;

    public function definition(): array
    {
        return [
            'attribute_id' => \App\Models\Attribute::factory(),
            'value' => fake()->randomElement(['Red', 'Blue', 'Small', 'Medium', 'Large', 'Cotton', 'Silk']),
            'sort' => fake()->numberBetween(0, 10),
        ];
    }
}
