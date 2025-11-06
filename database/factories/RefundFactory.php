<?php

namespace Database\Factories;

use App\Models\Refund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * RefundFactory
 */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'payment_id' => \App\Models\Payment::factory(),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'amount' => fake()->randomFloat(2, 1, 200),
            'status' => fake()->randomElement(['requested', 'approved', 'processed']),
            'processed_at' => null,
        ];
    }
}
