<?php

namespace App\Models;

/**
 * Currency Eloquent model.
 *
 * @author Abdul Wadood
 */
class Currency extends AbstractModel
{
    protected $fillable = [
        'code',
        'name',
    ];

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $timestamps = false;

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
