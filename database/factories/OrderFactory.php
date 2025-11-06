<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * OrderFactory
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'number' => intdiv(time(), 1),
            'user_id' => \App\Models\User::factory(),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'status' => fake()->randomElement(['pending', 'paid', 'picking']),
            'tax_inclusive' => false,
            'items_subtotal' => fake()->randomFloat(2, 10, 300),
            'total_discount' => 0.0,
            'total_tax' => fake()->randomFloat(2, 0, 30),
            'total_shipping' => fake()->randomFloat(2, 0, 20),
            'total' => fake()->randomFloat(2, 10, 350),
            'shipping_address' => json_encode([]),
            'billing_address' => json_encode([]),
        ];
    }
}
