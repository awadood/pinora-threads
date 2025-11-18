<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promotion\PromotionCouponRequest;
use App\Http\Resources\Promotion\PromotionCouponResource;
use App\Models\Promotion;
use App\Models\PromotionCoupon;
use App\Repositories\Promotion\Contracts\IPromotionCouponRepository;

/**
 * PromotionCouponController
 *
 * Admin CRUD for coupons under a promotion.
 *
 * @author Abdul Wadood
 */
class PromotionCouponController extends Controller
{
    public function __construct(protected IPromotionCouponRepository $couponRepository) {}

    /**
     * POST /api/promotions/{promotion}/coupons
     */
    public function store(PromotionCouponRequest $request, Promotion $promotion)
    {
        $data = $request->validated();
        $data['promotion_id'] = $promotion->id;

        /** @var PromotionCoupon $coupon */
        $coupon = $this->couponRepository->create($data);

        return PromotionCouponResource::make($coupon)->response()->setStatusCode(201);
    }

    /**
     * PUT /api/promotion-coupons/{coupon}
     */
    public function update(PromotionCouponRequest $request, PromotionCoupon $coupon)
    {
        $data = $request->validated();

        $coupon->fill($data)->save();

        return PromotionCouponResource::make($coupon);
    }

    /**
     * DELETE /api/promotion-coupons/{coupon}
     */
    public function destroy(PromotionCoupon $coupon)
    {
        $this->couponRepository->disableIfNotDestroy($coupon);

        return response()->json([], 204);
    }
}
