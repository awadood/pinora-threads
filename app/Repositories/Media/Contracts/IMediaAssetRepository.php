<?php

namespace App\Repositories\Media\Contracts;

use App\Models\MediaAsset;
use App\Repositories\IBaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IMediaAssetRepository extends IBaseRepository
{
    /**
     * Paginated listing for media assets for admin search/browse.
     *
     * Semantics:
     * - Supports filtering by type (image/video) and free-text search on key/cdn_url/checksum.
     */
    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator;

    /**
     * Create/register a media asset.
     *
     * Semantics:
     * - Inserts into media_assets.
     * - When type=video, caller also creates/updates media_videos separately.
     */
    public function register(array $data): MediaAsset;

    /**
     * Fetch a media asset with renditions + video metadata (if present).
     */
    public function findWithDetails(int $id): ?MediaAsset;

    /**
     * Delete an asset only when it has zero attachments.
     *
     * Semantics:
     * - Hard delete media_assets row.
     * - Throws ValidationException if asset is still attached anywhere.
     */
    public function deleteIfOrphan(MediaAsset $asset): int;
}
