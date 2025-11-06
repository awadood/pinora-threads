<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PromotionCoupon Eloquent model.
 *
 * @author Abdul Wadood
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
