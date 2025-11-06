<?php

namespace Database\Factories;

use App\Models\TaxRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TaxRuleFactory
 */
class TaxRuleFactory extends Factory
{
    protected $model = TaxRule::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->bothify('TAX-###')),
            'priority' => fake()->numberBetween(1, 3),
            'position' => fake()->numberBetween(1, 3),
            'calculate_subtotal' => fake()->boolean(),
            'applies_to_shipping' => fake()->boolean(),
            'active' => true,
        ];
    }
}
