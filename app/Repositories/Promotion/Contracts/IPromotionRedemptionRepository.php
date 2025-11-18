<?php

namespace App\Repositories\Promotion\Contracts;

use App\Models\PromotionRedemption;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * IPromotionRedemptionRepository
 *
 * Contract for reading and recording promotion redemptions for
 * reporting and audit (per user, per promotion, per order).
 *
 * @author Abdul Wadood
 */
interface IPromotionRedemptionRepository extends IBaseRepository
{
    /**
     * List redemptions for a given promotion ordered by newest first.
     *
     * @return Collection<int, PromotionRedemption>
     */
    public function findByPromotion(int $promotionId): Collection;
}
