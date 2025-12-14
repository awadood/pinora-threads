<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * A concrete SKU derived from a product’s attributes (size/color etc.).
 *
 * @author Abdul Wadood
 */
class ProductVariant extends AbstractLoggableModel
{
    use HasMedia;

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

    public function thumbnailMedia(): MorphOne
    {
        return $this->primaryMediaForRole('thumbnail');
    }

    public function galleryMedia(): MorphMany
    {
        return $this->mediaForRole('gallery');
    }
}
