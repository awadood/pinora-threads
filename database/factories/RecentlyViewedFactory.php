<?php

namespace Database\Factories;

use App\Models\RecentlyViewed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * RecentlyViewedFactory
 */
class RecentlyViewedFactory extends Factory
{
    protected $model = RecentlyViewed::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'product_id' => \App\Models\Product::factory(),
            'product_variant_id' => null,
            'viewed_at' => now(),
        ];
    }
}
