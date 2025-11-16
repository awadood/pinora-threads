<?php

namespace App\Repositories\Engagement;

use App\Models\Lookbook;
use App\Repositories\BaseRepository;
use App\Repositories\Engagement\Contracts\ILookbookRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * LookbookRepository
 *
 * Eloquent-based repository for lookbooks (campaigns).
 *
 * @author Abdul Wadood
 */
class LookbookRepository extends BaseRepository implements ILookbookRepository
{
    protected string $modelClass = Lookbook::class;

    public function findBySlug(string $slug): ?Lookbook
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function getPublicLookbooks(): Collection
    {
        return $this->query()
            ->where('active', true)
            ->whereNotNull('published_at')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get();
    }
}
