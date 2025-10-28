<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PromotionCouponUse
 *
 * @author Abdul Wadood
 */
class PromotionCouponUse extends AbstractModel
{
    protected $fillable = [
        'times_used',
        'promotion_coupon_id',
        'customer_id',
    ];

    public $timestamps = false;

    // Relationships

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(PromotionCoupon::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
