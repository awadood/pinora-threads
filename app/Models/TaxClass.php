<?php

namespace App\Models;

/**
 * TaxClass Eloquent model.
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
