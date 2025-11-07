<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TaxCalculation Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $tax_rate_id
 * @property int $tax_rule_id
 * @property int $user_tax_class_id
 * @property int $product_tax_class_id
 * @property-read \App\Models\TaxClass $productTaxClass
 * @property-read \App\Models\TaxRate $taxRate
 * @property-read \App\Models\TaxRule $taxRule
 * @property-read \App\Models\TaxClass $userTaxClass
 *
 * @method static \Database\Factories\TaxCalculationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereProductTaxClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereTaxRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxCalculation whereUserTaxClassId($value)
 *
 * @mixin \Eloquent
 */
class TaxCalculation extends AbstractModel
{
    protected $fillable = [
        'tax_rate_id',
        'tax_rule_id',
        'user_tax_class_id',
        'product_tax_class_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    // Lifecycle

    // Relationships

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function taxRule(): BelongsTo
    {
        return $this->belongsTo(TaxRule::class);
    }

    public function userTaxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'user_tax_class_id');
    }

    public function productTaxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'product_tax_class_id');
    }
}
