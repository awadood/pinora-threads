<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class PromotionCoupon
 *
 * @author Abdul Wadood
 */
class PromotionCoupon extends AbstractModel
{
    protected $fillable = [
        'code',
        'usage_limit',
        'usage_per_customer',
        'times_used',
        'expiry',
        'promotion_id',
    ];

    // Relationships

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function couponUses(): HasMany
    {
        return $this->hasMany(PromotionCouponUse::class);
    }
}
