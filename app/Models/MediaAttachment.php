<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic link between media assets and owning models with role/ordering.
 *
 * @author Abdul Wadood
 */
class MediaAttachment extends AbstractLoggableModel
{
    protected $fillable = [
        'media_asset_id',
        'owner_type',
        'owner_id',
        'role',
        'position',
        'is_primary',
        'alt_text',
        'caption',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id', 'id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
