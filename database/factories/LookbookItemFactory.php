<?php

namespace Database\Factories;

use App\Models\LookbookItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * LookbookItemFactory
 */
class LookbookItemFactory extends Factory
{
    protected $model = LookbookItem::class;

    public function definition(): array
    {
        return [
            'lookbook_id' => \App\Models\Lookbook::factory(),
            'title' => fake()->optional()->sentence(3),
            'image_url' => fake()->imageUrl(1600, 900, 'fashion', true),
            'notes' => fake()->optional()->sentence(),
            'sort_order' => 0,
        ];
    }
}
