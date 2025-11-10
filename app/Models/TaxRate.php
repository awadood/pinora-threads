<?php

namespace App\Models;

/**
 * Defines a tax percentage/amount applicable to a jurisdiction or class.
 *
 * @author Abdul Wadood
 */
class TaxRate extends AbstractModel
{
    protected $fillable = [
        'code',
        'amount',
        'percentage',
        'refundable',
        'country_code',
        'state_code',
        'zipcode',
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
            'percentage' => 'boolean',
            'refundable' => 'boolean',
            'zip_is_range' => 'boolean',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships
}
