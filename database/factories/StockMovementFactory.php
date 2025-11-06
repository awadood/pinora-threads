<?php

namespace Database\Factories;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * StockMovementFactory
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'stock_id' => \App\Models\Stock::factory(),
            'variant_id' => \App\Models\ProductVariant::factory(),
            'type' => fake()->randomElement(['purchase', 'sale', 'refund', 'adjustment', 'transfer_in', 'transfer_out']),
            'quantity_delta' => fake()->numberBetween(-5, 10),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}
