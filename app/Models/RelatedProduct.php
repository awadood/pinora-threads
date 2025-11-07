<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RelatedProduct Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $product_id
 * @property int $related_product_id
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Product $related
 *
 * @method static \Database\Factories\RelatedProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedProduct whereRelatedProductId($value)
 *
 * @mixin \Eloquent
 */
class RelatedProduct extends AbstractModel
{
    protected $fillable = [
        'product_id',
        'related_product_id',
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
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function related(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}
