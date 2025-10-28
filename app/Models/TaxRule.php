<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class TaxRule
 *
 * @author Abdul Wadood
 */
class TaxRule extends AbstractModel
{
    protected $fillable = [
        'code',
        'priority',
        'position',
        'calculate_subtotal',
        'active',
    ];

    // Relationships

    public function calculations(): HasMany
    {
        return $this->hasMany(TaxCalculation::class);
    }
}
