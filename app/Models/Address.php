<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Address Eloquent model.
 *
 * @author Abdul Wadood
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
