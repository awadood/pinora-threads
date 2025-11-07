<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockBackInSubscription Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $variant_id
 * @property int|null $user_id
 * @property string|null $email
 * @property string|null $notified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\StockBackInSubscriptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription whereNotifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockBackInSubscription whereVariantId($value)
 *
 * @mixin \Eloquent
 */
class StockBackInSubscription extends AbstractModel
{
    protected $fillable = [
        'variant_id',
        'user_id',
        'email',
        'notified_at',
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

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
