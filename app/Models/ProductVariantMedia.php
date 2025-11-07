<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductVariantMedia Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $product_variant_id
 * @property string $type
 * @property string $url
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\ProductVariantMediaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantMedia whereUrl($value)
 *
 * @mixin \Eloquent
 */
class ProductVariantMedia extends AbstractModel
{
    protected $fillable = [
        'product_variant_id',
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

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
