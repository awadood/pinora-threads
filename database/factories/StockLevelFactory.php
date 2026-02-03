<?php

namespace Database\Factories;

use App\Models\StockLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * StockLevelFactory
 */
class StockLevelFactory extends Factory
{
    protected $model = StockLevel::class;

    public function definition(): array
    {
        return [
            'stock_id' => \App\Models\Stock::factory(),
            'product_id' => \App\Models\Product::factory(),
            'quantity' => fake()->numberBetween(0, 500),
            'notify_below' => 50,
            'allow_backorder' => false,
            'promised_at' => null,
            'restock_eta' => null,
        ];
    }
}
