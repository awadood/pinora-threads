<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Manages coupon codes, expiries, and usage limits for promotions.
 *
 * @author Abdul Wadood
 */
class PromotionCoupon extends AbstractLoggableModel
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
