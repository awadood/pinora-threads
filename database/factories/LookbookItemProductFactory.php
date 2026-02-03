<?php

namespace Database\Factories;

use App\Models\LookbookItemProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * LookbookItemProductFactory
 */
class LookbookItemProductFactory extends Factory
{
    protected $model = LookbookItemProduct::class;

    public function definition(): array
    {
        return [
            'lookbook_item_id' => \App\Models\LookbookItem::factory(),
            'product_id' => \App\Models\Product::factory(),
            'sort_order' => 0,
        ];
    }
}
