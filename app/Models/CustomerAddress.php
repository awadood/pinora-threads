<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a user’s shipping or billing address with country/state linkage.
 *
 * @author Abdul Wadood
 */
class CustomerAddress extends AbstractLoggableModel
{
    protected $table = 'customer_addresses';

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
    ];

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
