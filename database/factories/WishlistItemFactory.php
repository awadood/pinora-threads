<?php

namespace Database\Factories;

use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * WishlistItemFactory
 */
class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    public function definition(): array
    {
        return [
            'wishlist_id' => \App\Models\Wishlist::factory(),
            'product_id' => \App\Models\Product::factory(),
            'product_variant_id' => null,
        ];
    }
}
