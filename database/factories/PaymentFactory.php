<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * PaymentFactory
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'invoice_id' => null,
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'method' => fake()->randomElement(['stripe', 'paypal', 'payfast', 'cod']),
            'action' => fake()->randomElement(['auth', 'capture', 'sale', 'cod_collection']),
            'status' => fake()->randomElement(['pending', 'succeeded']),
            'amount' => fake()->randomFloat(2, 10, 400),
            'gateway_txn_id' => strtoupper(fake()->bothify('TXN########')),
            'processed_at' => now(),
        ];
    }
}
