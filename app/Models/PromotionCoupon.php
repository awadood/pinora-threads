<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PromotionCoupon Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $promotion_id
 * @property string $code
 * @property int|null $usage_limit
 * @property int|null $usage_per_user
 * @property string|null $expiry
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Promotion $promotion
 *
 * @method static \Database\Factories\PromotionCouponFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon whereExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon wherePromotionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon whereUsageLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionCoupon whereUsagePerUser($value)
 *
 * @mixin \Eloquent
 */
class PromotionCoupon extends AbstractModel
{
    protected $fillable = [
        'promotion_id',
        'code',
        'usage_limit',
        'usage_per_user',
        'expiry',
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
}
