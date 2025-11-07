<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PromotionRedemption Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $promotion_id
 * @property int|null $promotion_coupon_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property string $redeemed_at
 * @property string $currency_code
 * @property string $cart_amount
 * @property string $discount_amount
 * @property string|null $idempotency_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PromotionCoupon|null $coupon
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\Promotion $promotion
 * @property-read \App\Models\User|null $user
 *
 * @method static \Database\Factories\PromotionRedemptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereCartAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereIdempotencyKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption wherePromotionCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption wherePromotionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereRedeemedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionRedemption whereUserId($value)
 *
 * @mixin \Eloquent
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
