<?php

namespace App\Models;

/**
 * Supported currency with ISO-4217 code and formatting for prices.
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

    public $incrementing = false;

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
