<?php

namespace App\Repositories\Media\Contracts;

use App\Models\MediaAsset;
use App\Repositories\IBaseRepository;
use Illuminate\Support\Collection;

interface IMediaRenditionRepository extends IBaseRepository
{
    /**
     * List all renditions for a given asset.
     */
    public function listForAsset(MediaAsset $asset): Collection;

    /**
     * Bulk upsert renditions for an asset by (media_asset_id, profile).
     *
     * Semantics:
     * - Inserts missing profiles.
     * - Updates existing profiles fields (disk/key/dimensions/bytes/mime_type).
     */
    public function upsertForAsset(MediaAsset $asset, array $profiles): int;
}
