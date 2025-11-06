<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CurrencyFactory
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'code' => fake()->randomElement(['USD', 'PKR']),
            'name' => fake()->randomElement(['US Dollar', 'Pakistani Rupee']),
        ];
    }
}
