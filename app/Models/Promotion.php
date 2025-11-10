<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Defines rule-based incentives (conditions and actions) for carts/orders.
 *
 * @author Abdul Wadood
 */
class Promotion extends AbstractLoggableModel
{
    protected $fillable = [
        'title',
        'from_date',
        'to_date',
        'applies_via',
        'usage_per_user',
        'rules',
        'sort_order',
        'active',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'rules' => 'array',
        ];
    }

    // Lifecycle

    // Relationships

    public function coupons(): HasMany
    {
        return $this->hasMany(PromotionCoupon::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromotionRedemption::class);
    }
}
