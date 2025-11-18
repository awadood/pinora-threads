<?php

namespace App\Repositories\Promotion;

use App\Models\Promotion;
use App\Repositories\BaseRepository;
use App\Repositories\Promotion\Contracts\IPromotionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * PromotionRepository
 *
 * Eloquent-backed repository for Promotions, with helpers to
 * fetch active storefront promotions and support admin listing.
 *
 * @author Abdul Wadood
 */
class PromotionRepository extends BaseRepository implements IPromotionRepository
{
    /**
     * @var class-string<Promotion>
     */
    protected string $modelClass = Promotion::class;

    /**
     * {@inheritDoc}
     */
    public function getPublicActive(Carbon $now): Collection
    {
        return $this->query()
            ->where('active', true)
            ->where('status', 'ongoing')
            ->where('from_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('to_date')
                    ->orWhere('to_date', '>=', $now);
            })
            ->orderBy('sort_order')
            ->get();
    }
}
