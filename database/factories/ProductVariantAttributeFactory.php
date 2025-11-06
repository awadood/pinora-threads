<?php

namespace Database\Factories;

use App\Models\ProductVariantAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductVariantAttributeFactory
 */
class ProductVariantAttributeFactory extends Factory
{
    protected $model = ProductVariantAttribute::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => \App\Models\ProductVariant::factory(),
            'attribute_id' => \App\Models\Attribute::factory(),
            'option_id' => null,
            'value' => null,
        ];
    }
}
