<?php

namespace App\Repositories\Media;

use App\Models\MediaAsset;
use App\Repositories\BaseRepository;
use App\Repositories\Media\Contracts\IMediaAssetRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MediaAssetRepository extends BaseRepository implements IMediaAssetRepository
{
    protected string $modelClass = MediaAsset::class;

    protected array $allowedSearchColumns = [
        'type' => true,
        'key' => true,
        'cdn_url' => true,
        'checksum' => true,
        'mime_type' => true,
    ];

    /**
     * Override: media_assets table has no 'active' column.
     */
    public function create(array $attributes): MediaAsset
    {
        /** @var MediaAsset $asset */
        $asset = $this->query()->create($attributes);

        return $asset;
    }

    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $q = $this->query();

        if (! empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        if (! empty($filters['q'])) {
            $term = (string) $filters['q'];
            $q->where(function ($qq) use ($term) {
                $qq->where('key', 'like', "%{$term}%")
                    ->orWhere('cdn_url', 'like', "%{$term}%")
                    ->orWhere('checksum', 'like', "%{$term}%");
            });
        }

        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function register(array $data): MediaAsset
    {
        /** @var MediaAsset $asset */
        $asset = $this->create($data);

        return $asset;
    }

    public function findWithDetails(int $id): ?MediaAsset
    {
        return $this->query()
            ->with(['renditions', 'video'])
            ->find($id);
    }

    public function deleteIfOrphan(MediaAsset $asset): int
    {
        $asset->loadCount('attachments');

        if ((int) $asset->attachments_count > 0) {
            throw ValidationException::withMessages([
                'media_asset' => 'Asset cannot be deleted because it is attached to one or more owners.',
            ]);
        }

        return DB::transaction(function () use ($asset) {
            return $asset->delete() ? 1 : 0;
        });
    }
}
