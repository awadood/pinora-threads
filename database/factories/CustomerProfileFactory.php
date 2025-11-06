<?php

namespace Database\Factories;

use App\Models\CustomerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CustomerProfileFactory
 */
class CustomerProfileFactory extends Factory
{
    protected $model = CustomerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'tax_class_id' => \App\Models\TaxClass::factory(),
            'marketing_email_opt_in' => fake()->boolean(),
            'marketing_sms_opt_in' => fake()->boolean(),
            'preferred_currency' => fake()->randomElement(['USD', 'PKR']),
        ];
    }
}
