<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores product media references (images, video) and ordering.
 *
 * @author Abdul Wadood
 */
class ProductMedia extends AbstractLoggableModel
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
