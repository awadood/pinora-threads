<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Logs applied promotions/coupons with discount amounts and actors.
 *
 * @author Abdul Wadood
 */
class PromotionRedemption extends AbstractModel
{
    protected $fillable = [
        'promotion_id',
        'promotion_coupon_id',
        'user_id',
        'order_id',
        'redeemed_at',
        'currency_code',
        'cart_amount',
        'discount_amount',
        'idempotency_key',
    ];

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

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(PromotionCoupon::class, 'promotion_coupon_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
