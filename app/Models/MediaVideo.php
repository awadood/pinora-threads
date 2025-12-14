<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Video-specific metadata tied to a canonical media asset.
 *
 * @author Abdul Wadood
 */
class MediaVideo extends AbstractLoggableModel
{
    protected $primaryKey = 'media_asset_id';

    public $incrementing = false;

    protected $fillable = [
        'media_asset_id',
        'provider',
        'external_id',
        'duration_seconds',
        'poster_media_asset_id',
        'autoplay',
        'muted',
        'loop',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'autoplay' => 'boolean',
            'muted' => 'boolean',
            'loop' => 'boolean',
        ];
    }

    // Lifecycle

    // Relationships

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id', 'id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'poster_media_asset_id', 'id');
    }
}
