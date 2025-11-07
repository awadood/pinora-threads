<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RecentlyViewed Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\ProductVariant|null $variant
 *
 * @method static \Database\Factories\RecentlyViewedFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecentlyViewed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecentlyViewed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecentlyViewed query()
 *
 * @mixin \Eloquent
 */
class RecentlyViewed extends AbstractModel
{
    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_id',
        'viewed_at',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
