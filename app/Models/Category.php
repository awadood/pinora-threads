<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Organizes products into a navigable taxonomy for browsing and SEO.
 *
 * @author Abdul Wadood
 */
class Category extends AbstractLoggableModel
{
    use HasMedia;
    use HasSeoMeta;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'sort',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function heroMedia(): MorphOne
    {
        return $this->primaryMediaForRole('hero');
    }

    public function ogImageMedia(): MorphOne
    {
        return $this->primaryMediaForRole('og_image');
    }

    public function thumbnailMedia(): MorphOne
    {
        return $this->primaryMediaForRole('thumbnail');
    }
}
