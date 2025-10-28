<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TaxCalculation
 *
 * @author Abdul Wadood
 */
class TaxCalculation extends AbstractModel
{
    protected $fillable = [
        'tax_rate_id',
        'tax_rule_id',
        'customer_tax_class_id',
        'product_tax_class_id',
    ];

    // Relationships

    public function rate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(TaxRule::class);
    }

    public function customerTaxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'customer_tax_class_id');
    }

    public function productTaxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'product_tax_class_id');
    }
}
