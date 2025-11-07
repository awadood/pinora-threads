<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LookbookItemProduct Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $lookbook_item_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LookbookItem $lookbookItem
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\ProductVariant|null $variant
 *
 * @method static \Database\Factories\LookbookItemProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct whereLookbookItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItemProduct whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LookbookItemProduct extends AbstractModel
{
    protected $fillable = [
        'lookbook_item_id',
        'product_id',
        'product_variant_id',
        'sort_order',
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

    public function lookbookItem(): BelongsTo
    {
        return $this->belongsTo(LookbookItem::class);
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
