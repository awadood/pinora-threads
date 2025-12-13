<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ISO-standard country definition used by addresses and taxation.
 *
 * @author Abdul Wadood
 */
class Country extends AbstractLoggableModel
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

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
