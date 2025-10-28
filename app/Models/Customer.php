<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Customer
 *
 * @author Abdul Wadood
 */
class Customer extends AbstractModel
{
    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'profession',
        'dob',
        'nationality',
        'booking_allowed',
        'conditions',    // it may contain skin/hair conditions
        'notes',    // any particular notes about customer which are not related to bookings.
        'group_id',
    ];

    // Lifecycle

    // Relationships

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'group_id');
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_customer')
            ->withPivot('times_used')
            ->withTimestamps();
    }

    public function couponUses(): HasMany
    {
        return $this->hasMany(PromotionCouponUse::class);
    }

    public function arrears(): HasMany
    {
        return $this->hasMany(CustomerArrear::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }
}
