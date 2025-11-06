<?php

namespace Database\Factories;

use App\Models\PaymentAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * PaymentAttemptFactory
 */
class PaymentAttemptFactory extends Factory
{
    protected $model = PaymentAttempt::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'payment_id' => null,
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'method' => fake()->randomElement(['stripe', 'paypal', 'payfast', 'cod']),
            'action' => fake()->randomElement(['auth', 'capture', 'sale', 'cod_collection']),
            'status' => fake()->randomElement(['pending', 'succeeded', 'failed', 'requires_action']),
            'amount' => fake()->randomFloat(2, 0, 400),
            'attempted_at' => now(),
        ];
    }
}
