<?php

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CustomerGroupFactory
 */
class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['VIP', 'Wholesale', 'Retail']),
            'active' => true,
        ];
    }
}
