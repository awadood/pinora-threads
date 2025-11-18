<?php

namespace App\Repositories\Promotion\Contracts;

use App\Models\PromotionCoupon;
use App\Repositories\IBaseRepository;

/**
 * IPromotionCouponRepository
 *
 * Contract for managing promotion coupons: CRUD plus lookups by code.
 *
 * @author Abdul Wadood
 */
interface IPromotionCouponRepository extends IBaseRepository
{
    /**
     * Find a coupon by its unique code.
     */
    public function findByCode(string $code): ?PromotionCoupon;
}
