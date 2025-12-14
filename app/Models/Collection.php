<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Curates themed product groupings for merchandising campaigns.
 *
 * @author Abdul Wadood
 */
class Collection extends AbstractLoggableModel
{
    use HasMedia;

    protected $fillable = [
        'name',
        'slug',
        'sort',
        'notes',
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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product')->withPivot(['sort'])->withTimestamps();
    }

    public function heroMedia(): MorphOne
    {
        return $this->primaryMediaForRole('hero');
    }

    public function ogImageMedia(): MorphOne
    {
        return $this->primaryMediaForRole('og_image');
    }
}
