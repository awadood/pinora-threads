<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CollectionProduct Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property-read \App\Models\Collection|null $collection
 * @property-read \App\Models\Product|null $product
 *
 * @method static \Database\Factories\CollectionProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionProduct query()
 *
 * @mixin \Eloquent
 */
class CollectionProduct extends AbstractModel
{
    protected $fillable = [
        'collection_id',
        'product_id',
        'sort',
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

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
