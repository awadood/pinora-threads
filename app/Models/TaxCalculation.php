<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TaxCalculation Eloquent model.
 *
 * @author Abdul Wadood
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
