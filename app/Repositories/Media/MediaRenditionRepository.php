<?php

namespace App\Repositories\Media;

use App\Models\MediaAsset;
use App\Models\MediaRendition;
use App\Repositories\BaseRepository;
use App\Repositories\Media\Contracts\IMediaRenditionRepository;
use Illuminate\Support\Collection;

class MediaRenditionRepository extends BaseRepository implements IMediaRenditionRepository
{
    protected string $modelClass = MediaRendition::class;

    /**
     * Override: media_renditions has no 'active' column.
     */
    public function create(array $attributes): MediaRendition
    {
        /** @var MediaRendition $r */
        $r = $this->query()->create($attributes);

        return $r;
    }

    public function listForAsset(MediaAsset $asset): Collection
    {
        return $this->query()
            ->where('media_asset_id', $asset->id)
            ->orderBy('profile')
            ->get();
    }

    public function upsertForAsset(MediaAsset $asset, array $profiles): int
    {
        $rows = [];
        foreach ($profiles as $p) {
            $rows[] = [
                'media_asset_id' => $asset->id,
                'profile' => $p['profile'],
                'disk' => $p['disk'] ?? 's3',
                'key' => $p['key'],
                'cdn_url' => $p['cdn_url'] ?? null,
                'mime_type' => $p['mime_type'] ?? null,
                'bytes' => $p['bytes'] ?? null,
                'width' => $p['width'] ?? null,
                'height' => $p['height'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($rows) === 0) {
            return 0;
        }

        MediaRendition::upsert(
            $rows,
            ['media_asset_id', 'profile'],
            ['disk', 'key', 'cdn_url', 'mime_type', 'bytes', 'width', 'height', 'updated_at']
        );

        return count($rows);
    }
}
