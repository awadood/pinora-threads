<?php

namespace Database\Factories;

use App\Models\TaxClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TaxClassFactory
 */
class TaxClassFactory extends Factory
{
    protected $model = TaxClass::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Standard', 'Reduced', 'Zero']),
        ];
    }
}
