<?php

namespace Database\Factories;

use App\Models\CustomerAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CustomerAccountFactory
 */
class CustomerAccountFactory extends Factory
{
    protected $model = CustomerAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'marketing_email_opt_in' => fake()->boolean(),
            'marketing_sms_opt_in' => fake()->boolean(),
            'preferred_currency' => fake()->randomElement(['USD', 'PKR']),
            'default_shipping_address_id' => null,
            'default_billing_address_id' => null,
        ];
    }
}
