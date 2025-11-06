<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * InvoiceFactory
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'number' => fake()->unique()->numerify('100########'),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'amount_due' => fake()->randomFloat(2, 10, 400),
            'status' => fake()->randomElement(['issued', 'paid']),
            'issued_at' => now(),
            'due_at' => null,
            'paid_at' => null,
        ];
    }
}
