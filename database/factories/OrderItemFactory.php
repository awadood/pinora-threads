<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * OrderItemFactory
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'product_id' => \App\Models\Product::factory(),
            'product_variant_id' => \App\Models\ProductVariant::factory(),
            'product_name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->bothify('SKU-#####')),
            'variant' => json_encode([]),
            'quantity' => fake()->numberBetween(1, 3),
            'unit_price' => fake()->randomFloat(2, 10, 200),
            'subtotal' => fake()->randomFloat(2, 10, 400),
            'discount' => 0.0,
            'tax' => fake()->randomFloat(2, 0, 20),
            'total' => fake()->randomFloat(2, 10, 400),
        ];
    }
}
