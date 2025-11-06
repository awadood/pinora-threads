<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * StateFactory
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('??')),
            'name' => fake()->state(),
            'country_code' => 'US',
        ];
    }
}
