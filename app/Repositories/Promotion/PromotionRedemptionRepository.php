<?php

namespace App\Repositories\Promotion;

use App\Models\PromotionRedemption;
use App\Repositories\BaseRepository;
use App\Repositories\Promotion\Contracts\IPromotionRedemptionRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * PromotionRedemptionRepository
 *
 * Eloquent-backed repository for PromotionRedemption records,
 * used mainly for admin analytics and reporting.
 *
 * @author Abdul Wadood
 */
class PromotionRedemptionRepository extends BaseRepository implements IPromotionRedemptionRepository
{
    /**
     * @var class-string<PromotionRedemption>
     */
    protected string $modelClass = PromotionRedemption::class;

    public function findByPromotion(int $promotionId): Collection
    {
        return $this->query()
            ->where('promotion_id', $promotionId)
            ->orderByDesc('redeemed_at')
            ->get();
    }
}
