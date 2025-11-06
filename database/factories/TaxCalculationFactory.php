<?php

namespace Database\Factories;

use App\Models\TaxCalculation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TaxCalculationFactory
 */
class TaxCalculationFactory extends Factory
{
    protected $model = TaxCalculation::class;

    public function definition(): array
    {
        return [
            'tax_rate_id' => \App\Models\TaxRate::factory(),
            'tax_rule_id' => \App\Models\TaxRule::factory(),
            'user_tax_class_id' => \App\Models\TaxClass::factory(),
            'product_tax_class_id' => \App\Models\TaxClass::factory(),
        ];
    }
}
