<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CustomerProfile
 *
 * @author Abdul Wadood
 */
class CustomerProfile extends AbstractModel
{
    protected $fillable = [
        'total_booking_services',
        'canceled_booking_services',
        'refunded_booking_services',
        'total_purchased_products',
        'total_payments',
        'outstanding_balance',
        'customer_id',
    ];

    // Relationships

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
