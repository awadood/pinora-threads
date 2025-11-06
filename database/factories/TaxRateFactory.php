<?php

namespace Database\Factories;

use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TaxRateFactory
 */
class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->bothify('US-??-*-R#')),
            'amount' => fake()->randomFloat(2, 0, 15),
            'percentage' => true,
            'refundable' => true,
            'country_code' => 'US',
            'state_code' => null,
            'zipcode' => fake()->postcode(),
            'zip_is_range' => false,
            'zip_from' => null,
            'zip_to' => null,
            'active' => true,
        ];
    }
}
