<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RelatedProduct Eloquent model.
 *
 * @author Abdul Wadood
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
