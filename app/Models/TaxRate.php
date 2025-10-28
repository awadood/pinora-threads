<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class TaxRate
 *
 * @author Abdul Wadood
 */
class TaxRate extends AbstractModel
{
    protected $fillable = [
        'code',
        'amount',
        'is_percentage',
        'refundable',
        'country_code',
        'region_code',
        'post_code',
        'zip_is_range',
        'zip_from',
        'zip_to',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    // Relationships

    public function calculations(): HasMany
    {
        return $this->hasMany(TaxCalculation::class);
    }
}
