<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Collection Eloquent model.
 *
 * @author Abdul Wadood
 */
class Collection extends AbstractLoggableModel
{
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
}
