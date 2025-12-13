<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * First-level administrative division linked to a country.
 *
 * @author Abdul Wadood
 */
class State extends AbstractLoggableModel
{
    protected $fillable = [
        'code',
        'name',
        'country_code',
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
