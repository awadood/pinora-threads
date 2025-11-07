<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Address Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $label
 * @property string|null $name
 * @property string $line1
 * @property string|null $line2
 * @property string $city
 * @property string|null $state_code
 * @property string|null $postal_code
 * @property string $country_code
 * @property string|null $phone
 * @property bool $default_shipping
 * @property bool $default_billing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Country $country
 * @property-read \App\Models\State|null $state
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\AddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereDefaultBilling($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereDefaultShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereStateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Address extends AbstractLoggableModel
{
    protected $fillable = [
        'user_id',
        'label',
        'name',
        'line1',
        'line2',
        'city',
        'state_code',
        'postal_code',
        'country_code',
        'phone',
        'default_shipping',
        'default_billing',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_shipping' => 'boolean',
            'default_billing' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_code', 'code');
    }
}
