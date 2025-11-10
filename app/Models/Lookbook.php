<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Editorial lookbook that showcases themed product stories.
 *
 * @author Abdul Wadood
 */
class Lookbook extends AbstractLoggableModel
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'active',
        'sort_order',
        'published_at',
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

    public function items(): HasMany
    {
        return $this->hasMany(LookbookItem::class);
    }
}
