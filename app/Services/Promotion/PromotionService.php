<?php

namespace App\Services\Promotion;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionCoupon;
use App\Models\User;
use App\Repositories\Promotion\Contracts\IPromotionCouponRepository;
use App\Repositories\Promotion\Contracts\IPromotionRedemptionRepository;
use App\Repositories\Promotion\Contracts\IPromotionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * PromotionService
 *
 * Encapsulates high-level promotion logic:
 *  - Fetching active promotions for storefront
 *  - (Future) evaluation of rules against cart/order
 *  - Recording redemptions once a promotion is applied to an order
 *
 * This is where you'll plug the real rule engine later. For now,
 * it exposes safe primitives and a stub for future pricing hooks.
 *
 * @author Abdul Wadood
 */
class PromotionService
{
    public function __construct(
        protected IPromotionRepository $promotionRepository,
        protected IPromotionCouponRepository $couponRepository,
        protected IPromotionRedemptionRepository $redemptionRepository,
    ) {}

    /**
     * Active promotions for storefront (banners, marketing).
     *
     * @return Collection<int, Promotion>
     */
    public function getPublicActivePromotions(?Carbon $now = null): Collection
    {
        $now ??= now();

        return $this->promotionRepository->getPublicActive($now);
    }

    /**
     * Find a coupon by code (for later use in OrderService).
     */
    public function findCouponByCode(string $code): ?PromotionCoupon
    {
        return $this->couponRepository->findByCode($code);
    }

    /**
     * Record a promotion redemption once you know the discount to apply.
     *
     * This should be called from the OrderService when an order is finalized.
     * For now it's a simple create wrapper; later you can enforce usage limits
     * and idempotency checks here.
     */
    public function recordRedemption(
        Promotion $promotion,
        ?PromotionCoupon $coupon,
        ?User $user,
        Order $order,
        string $currencyCode,
        float $cartAmount,
        float $discountAmount,
        ?string $idempotencyKey = null,
    ) {
        $data = [
            'promotion_id' => $promotion->id,
            'promotion_coupon_id' => $coupon?->id,
            'user_id' => $user?->id,
            'order_id' => $order->id,
            'redeemed_at' => now(),
            'currency_code' => $currencyCode,
            'cart_amount' => $cartAmount,
            'discount_amount' => $discountAmount,
            'idempotency_key' => $idempotencyKey,
        ];

        return $this->redemptionRepository->create($data);
    }
}
