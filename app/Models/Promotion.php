<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Promotion
 *
 * @author Abdul Wadood
 */
class Promotion extends AbstractModel
{
    protected $fillable = [
        'name',
        'description',
        'from_date',
        'to_date',
        'uses_per_customer',
        'rules_serialized',
        'sort_order',
        'times_used',
        'uses_per_coupon',
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
            'from_date' => 'date:Y-m-d H:i:s',
            'to_date' => 'date:Y-m-d H:i:s',
        ];
    }

    // Relationships

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'promotion_customer')
            ->withPivot('times_used')
            ->withTimestamps();
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(PromotionCoupon::class);
    }
}
