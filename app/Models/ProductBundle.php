<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bundles multiple products into a composite offering.
 *
 * @author Abdul Wadood
 */
class ProductBundle extends AbstractLoggableModel
{
    protected $fillable = [
        'product_id',
        'bundle_item_id',
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

    public function item(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bundle_item_id');
    }
}
