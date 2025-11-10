<?php

namespace App\Models;

/**
 * Links tax classes/rates with precedence and conditions.
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
        'applies_to_shipping',
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
            'calculate_subtotal' => 'boolean',
            'applies_to_shipping' => 'boolean',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships
}
