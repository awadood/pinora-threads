<?php

namespace App\Models;

/**
 * Describes tax applicability classes for products and customers.
 *
 * @author Abdul Wadood
 */
class TaxClass extends AbstractModel
{
    protected $fillable = [
        'name',
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
}
