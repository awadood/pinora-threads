<?php

namespace App\Repositories\Media\Contracts;

use App\Models\MediaAsset;
use App\Models\MediaVideo;
use App\Repositories\IBaseRepository;

interface IMediaVideoRepository extends IBaseRepository
{
    /**
     * Upsert video metadata for a video media asset.
     *
     * Semantics:
     * - Only valid when media_assets.type = 'video'.
     * - Keyed by media_asset_id (primary key).
     */
    public function upsertForAsset(MediaAsset $asset, array $data): MediaVideo;
}
