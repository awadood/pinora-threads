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
            'product_id' => \App\Models\Product::factory(),
            'stock_movement_type_code' => fake()->randomElement([
                \App\Models\StockMovementType::PURCHASE,
                \App\Models\StockMovementType::SALE,
                \App\Models\StockMovementType::REFUND,
                \App\Models\StockMovementType::ADJUSTMENT,
                \App\Models\StockMovementType::TRANSFER_IN,
                \App\Models\StockMovementType::TRANSFER_OUT,
            ]),
            'quantity_delta' => fake()->numberBetween(-5, 10),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}
