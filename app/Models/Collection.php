<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
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
    use HasSeoMeta;

    protected $fillable = [
        'name',
        'slug',
        'sort',
        'description',
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
        return $this->belongsToMany(Product::class, 'collection_product')->withPivot(['sort']);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'collection_country', 'collection_id', 'country_code');
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
