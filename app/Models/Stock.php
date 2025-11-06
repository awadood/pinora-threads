<?php

namespace App\Models;

/**
 * Stock Eloquent model.
 *
 * @author Abdul Wadood
 */
class Stock extends AbstractModel
{
    protected $fillable = [
        'title',
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
