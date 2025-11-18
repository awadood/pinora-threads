<?php

namespace App\Repositories\Promotion;

use App\Models\PromotionCoupon;
use App\Repositories\BaseRepository;
use App\Repositories\Promotion\Contracts\IPromotionCouponRepository;

/**
 * PromotionCouponRepository
 *
 * Eloquent-backed repository for PromotionCoupon entities, with
 * lookup support by coupon code for application during checkout.
 *
 * @author Abdul Wadood
 */
class PromotionCouponRepository extends BaseRepository implements IPromotionCouponRepository
{
    /**
     * @var class-string<PromotionCoupon>
     */
    protected string $modelClass = PromotionCoupon::class;

    public function findByCode(string $code): ?PromotionCoupon
    {
        return $this->query()->where('code', $code)->first();
    }
}
