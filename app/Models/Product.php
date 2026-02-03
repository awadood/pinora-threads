<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Represents a purchasable product with linked variants and catalog metadata.
 *
 * @author Abdul Wadood
 */
class Product extends AbstractLoggableModel
{
    use HasMedia;
    use HasSeoMeta;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'type',
        'description',
        'tax_class_id',
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
            'active' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variants', 'product_id', 'variant_id')->withTimestamps();
    }

    public function variantParents(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variants', 'variant_id', 'product_id')->withTimestamps();
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(ProductBundle::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_product')->withPivot(['sort'])->withTimestamps();
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id');
    }

    public function relatedByProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'related_product_id', 'product_id');
    }

    public function thumbnailMedia(): MorphOne
    {
        return $this->primaryMediaForRole('thumbnail');
    }

    public function heroMedia(): MorphOne
    {
        return $this->primaryMediaForRole('hero');
    }

    public function ogImageMedia(): MorphOne
    {
        return $this->primaryMediaForRole('og_image');
    }

    public function galleryMedia(): MorphMany
    {
        return $this->mediaForRole('gallery');
    }
}
