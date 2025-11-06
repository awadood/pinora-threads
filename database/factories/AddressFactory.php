<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * AddressFactory
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'label' => fake()->randomElement(['Home', 'Office', null]),
            'name' => fake()->name(),
            'line1' => fake()->streetAddress(),
            'line2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state_code' => null,
            'postal_code' => fake()->postcode(),
            'country_code' => fake()->randomElement(['US', 'PK']),
            'phone' => fake()->optional()->e164PhoneNumber(),
            'default_shipping' => false,
            'default_billing' => false,
        ];
    }
}
