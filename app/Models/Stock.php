<?php

namespace App\Models;

/**
 * Represents available stock at a logical inventory node (e.g., shop 1).
 *
 * @author Abdul Wadood
 */
class Stock extends AbstractLoggableModel
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
