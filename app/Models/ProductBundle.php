<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductBundle Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $product_id
 * @property int $product_variant_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $bundle
 * @property-read \App\Models\ProductVariant $variant
 *
 * @method static \Database\Factories\ProductBundleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductBundle extends AbstractModel
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'quantity',
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

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
