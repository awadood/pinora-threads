<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores media linked specifically to a product variant.
 *
 * @author Abdul Wadood
 */
class ProductVariantMedia extends AbstractLoggableModel
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
