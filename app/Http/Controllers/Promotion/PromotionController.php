<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promotion\PromotionRequest;
use App\Http\Resources\Promotion\PromotionResource;
use App\Models\Promotion;
use App\Repositories\Promotion\Contracts\IPromotionRepository;
use App\Services\Promotion\PromotionService;
use Illuminate\Http\Request;

/**
 * PromotionController
 *
 * Handles:
 *  - Public listing of active promotions (storefront)
 *  - Admin CRUD for promotions
 *
 * @author Abdul Wadood
 */
class PromotionController extends Controller
{
    public function __construct(
        protected IPromotionRepository $promotionRepository,
        protected PromotionService $promotionService,
    ) {}

    /**
     * GET /api/promotions
     *
     * Public: list active/ongoing promotions.
     */
    public function indexPublic()
    {
        $promotions = $this->promotionService->getPublicActivePromotions();

        return PromotionResource::collection($promotions);
    }

    /**
     * GET /api/promotions/{promotion}
     *
     * Public: show a single promotion if it is active.
     */
    public function showPublic(Promotion $promotion)
    {
        $now = now();

        $isActive = $promotion->active
            && $promotion->status === 'ongoing'
            && $promotion->from_date <= $now
            && (is_null($promotion->to_date) || $promotion->to_date >= $now);

        if (! $isActive) {
            abort(404);
        }

        return PromotionResource::make($promotion);
    }

    /**
     * GET /api/admin/promotions
     *
     * Admin listing with light filters.
     */
    public function index(Request $request)
    {
        $query = $this->promotionRepository->query();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('applies_via')) {
            $query->where('applies_via', $request->query('applies_via'));
        }

        if ($request->filled('active')) {
            $active = filter_var($request->query('active'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if (! is_null($active)) {
                $query->where('active', $active);
            }
        }

        $promotions = $query->orderBy('from_date', 'desc')->get();

        return PromotionResource::collection($promotions);
    }

    /**
     * GET /api/admin/promotions/{promotion}
     */
    public function show(Promotion $promotion)
    {
        return PromotionResource::make($promotion);
    }

    /**
     * POST /api/promotions
     */
    public function store(PromotionRequest $request)
    {
        $data = $request->validated();

        /** @var Promotion $promotion */
        $promotion = $this->promotionRepository->create($data);

        return PromotionResource::make($promotion)->response()->setStatusCode(201);
    }

    /**
     * PUT /api/promotions/{promotion}
     */
    public function update(PromotionRequest $request, Promotion $promotion)
    {
        $data = $request->validated();

        $promotion->fill($data)->save();

        return PromotionResource::make($promotion);
    }

    /**
     * DELETE /api/promotions/{promotion}
     *
     * Will try hard delete; on FK violation, BaseRepository falls back
     * to active=false if the model supports an 'active' attribute.
     */
    public function destroy(Promotion $promotion)
    {
        $this->promotionRepository->disableIfNotDestroy($promotion);

        return response()->json([], 204);
    }
}
