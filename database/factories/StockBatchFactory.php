<?php

namespace Database\Factories;

use App\Models\StockBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * StockBatchFactory
 */
class StockBatchFactory extends Factory
{
    protected $model = StockBatch::class;

    public function definition(): array
    {
        return [
            'stock_id' => \App\Models\Stock::factory(),
            'product_id' => \App\Models\Product::factory(),
            'received_at' => fake()->date(),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'unit_cost' => fake()->randomFloat(2, 5, 300),
            'qty_received' => fake()->numberBetween(1, 300),
            'qty_remaining' => fake()->numberBetween(1, 300),
        ];
    }
}
