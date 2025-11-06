<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ProductVariant Eloquent model.
 *
 * @author Abdul Wadood
 */
class ProductVariant extends AbstractLoggableModel
{
    protected $fillable = [
        'product_id',
        'sku',
        'title',
        'description',
        'default',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default' => 'boolean',
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductVariantPrice::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductVariantMedia::class);
    }
}
