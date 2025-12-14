<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Derived rendition of a media asset for specific size/profile usage.
 *
 * @author Abdul Wadood
 */
class MediaRendition extends AbstractLoggableModel
{
    protected $fillable = [
        'media_asset_id',
        'profile',
        'disk',
        'key',
        'cdn_url',
        'mime_type',
        'bytes',
        'width',
        'height',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    // Lifecycle

    // Relationships

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id', 'id');
    }
}
