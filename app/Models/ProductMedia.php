<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductMedia Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $product_id
 * @property string $type
 * @property string $url
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 *
 * @method static \Database\Factories\ProductMediaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductMedia whereUrl($value)
 *
 * @mixin \Eloquent
 */
class ProductMedia extends AbstractModel
{
    protected $fillable = [
        'product_id',
        'type',
        'url',
        'position',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
