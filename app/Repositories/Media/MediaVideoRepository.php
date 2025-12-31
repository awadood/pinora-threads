<?php

namespace App\Repositories\Media;

use App\Models\MediaAsset;
use App\Models\MediaVideo;
use App\Repositories\BaseRepository;
use App\Repositories\Media\Contracts\IMediaVideoRepository;
use Illuminate\Validation\ValidationException;

class MediaVideoRepository extends BaseRepository implements IMediaVideoRepository
{
    protected string $modelClass = MediaVideo::class;

    /**
     * Override: media_videos has no 'active' column.
     */
    public function create(array $attributes): MediaVideo
    {
        return $this->query()->create($attributes);
    }

    public function upsertForAsset(MediaAsset $asset, array $data): MediaVideo
    {
        if ($asset->type !== 'video') {
            throw ValidationException::withMessages([
                'media_asset' => 'Video metadata can only be set for assets of type video.',
            ]);
        }

        MediaVideo::updateOrCreate(
            ['media_asset_id' => $asset->id],
            [
                'provider' => $data['provider'] ?? null,
                'external_id' => $data['external_id'] ?? null,
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'poster_media_asset_id' => $data['poster_media_asset_id'] ?? null,
                'autoplay' => (bool) ($data['autoplay'] ?? false),
                'muted' => (bool) ($data['muted'] ?? true),
                'loop' => (bool) ($data['loop'] ?? false),
            ]
        );

        return $this->query()->where('media_asset_id', $asset->id)->firstOrFail();
    }
}
