<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LookbookItemProduct Eloquent model.
 *
 * @author Abdul Wadood
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
