<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Canonical stored media object (image or video) with metadata.
 *
 * @author Abdul Wadood
 */
class MediaAsset extends AbstractLoggableModel
{
    protected $fillable = [
        'type',
        'disk',
        'key',
        'cdn_url',
        'mime_type',
        'bytes',
        'width',
        'height',
        'alt_text',
        'title',
        'caption',
        'checksum',
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

    /**
     * Get the url for the given profile. if the profile does not exists then
     * either cdn_url or key is returned.
     *
     * @return string the selected url
     */
    public function urlFor(?string $profile): ?string
    {
        if ($profile) {
            $rendition = $this->renditions->firstWhere('profile', $profile);
            if ($rendition) {
                return $rendition->cdn_url ?: $rendition->key;
            }
        }

        return $this->cdn_url ?: $this->key;
    }

    // Lifecycle

    // Relationships

    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class, 'media_asset_id', 'id'); // FK columns are needed because of method name.);
    }

    public function renditions(): HasMany
    {
        return $this->hasMany(MediaRendition::class, 'media_asset_id', 'id'); // FK columns are needed because of method name.
    }

    public function video(): HasOne
    {
        return $this->hasOne(MediaVideo::class, 'media_asset_id', 'id'); // FK columns are needed because of method name.
    }

    public function posterForVideos(): HasMany
    {
        return $this->hasMany(MediaVideo::class, 'poster_media_asset_id', 'id');
    }
}
