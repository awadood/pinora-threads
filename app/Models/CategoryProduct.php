<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CategoryProduct Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Product|null $product
 *
 * @method static \Database\Factories\CategoryProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryProduct query()
 *
 * @mixin \Eloquent
 */
class CategoryProduct extends AbstractModel
{
    protected $fillable = [
        'category_id',
        'product_id',
    ];

    public $timestamps = false;

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
