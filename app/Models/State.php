<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * State Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property string $code
 * @property string $name
 * @property string $country_code
 * @property-read \App\Models\Country $country
 *
 * @method static \Database\Factories\StateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereName($value)
 *
 * @mixin \Eloquent
 */
class State extends AbstractModel
{
    protected $fillable = [
        'code',
        'name',
        'country_code',
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
}
