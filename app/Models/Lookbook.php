<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lookbook Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $cover_image
 * @property bool $active
 * @property int $sort_order
 * @property string|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LookbookItem> $items
 * @property-read int|null $items_count
 *
 * @method static \Database\Factories\LookbookFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookbook whereUpdatedAt($value)
 *
 * @mixin \Eloquent
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
