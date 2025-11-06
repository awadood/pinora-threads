<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * AttributeFactory
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('attr_????'),
            'label' => fake()->randomElement(['Color', 'Size', 'Fabric', 'Care']),
            'type' => fake()->randomElement(['text', 'select']),
            'active' => true,
        ];
    }
}
