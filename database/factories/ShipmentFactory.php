<?php

namespace Database\Factories;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ShipmentFactory
 */
class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'stock_id' => \App\Models\Stock::factory(),
            'method' => fake()->randomElement(['pickup', 'self', 'courier']),
            'carrier' => fake()->optional()->randomElement(['USPS', 'UPS', 'FEDEX', 'LEOPARDS', 'TCS']),
            'tracking_number' => fake()->optional()->bothify('TRK########'),
            'tracking_url' => null,
            'status' => fake()->randomElement(['pending', 'out_for_delivery', 'in_transit', 'delivered']),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'shipping_charge' => fake()->randomFloat(2, 0, 20),
            'shipping_cost' => fake()->randomFloat(2, 0, 15),
            'shipping_tax' => fake()->randomFloat(2, 0, 5),
        ];
    }
}
