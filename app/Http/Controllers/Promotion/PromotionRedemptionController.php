<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\PromotionRedemptionResource;
use App\Models\Promotion;
use App\Repositories\Promotion\Contracts\IPromotionRedemptionRepository;

/**
 * PromotionRedemptionController
 *
 * Read-only admin endpoints for viewing redemptions.
 *
 * @author Abdul Wadood
 */
class PromotionRedemptionController extends Controller
{
    public function __construct(protected IPromotionRedemptionRepository $redemptionRepository) {}

    /**
     * GET /api/promotions/{promotion}/redemptions
     */
    public function indexByPromotion(Promotion $promotion)
    {
        $redemptions = $this->redemptionRepository->findByPromotion($promotion->id);

        return PromotionRedemptionResource::collection($redemptions);
    }
}
