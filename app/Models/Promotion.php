<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Promotion Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property string $title
 * @property string $from_date
 * @property string|null $to_date
 * @property string $applies_via
 * @property int|null $usage_per_user
 * @property array<array-key, mixed> $rules
 * @property int $sort_order
 * @property bool $active
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PromotionCoupon> $coupons
 * @property-read int|null $coupons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PromotionRedemption> $redemptions
 * @property-read int|null $redemptions_count
 *
 * @method static \Database\Factories\PromotionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereAppliesVia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereUsagePerUser($value)
 *
 * @mixin \Eloquent
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
