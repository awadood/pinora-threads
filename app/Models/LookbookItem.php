<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LookbookItem Eloquent model.
 *
 * @author Abdul Wadood
 *
 * @property int $id
 * @property int $lookbook_id
 * @property string|null $title
 * @property string $image_url
 * @property string|null $notes
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Lookbook $lookbook
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LookbookItemProduct> $products
 * @property-read int|null $products_count
 *
 * @method static \Database\Factories\LookbookItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereLookbookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LookbookItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LookbookItem extends AbstractLoggableModel
{
    protected $fillable = [
        'lookbook_id',
        'title',
        'image_url',
        'notes',
        'sort_order',
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

    public function lookbook(): BelongsTo
    {
        return $this->belongsTo(Lookbook::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(LookbookItemProduct::class);
    }
}
