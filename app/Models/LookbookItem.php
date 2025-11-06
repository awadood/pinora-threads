<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LookbookItem Eloquent model.
 *
 * @author Abdul Wadood
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
