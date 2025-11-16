<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\RecentlyViewedResource;
use App\Services\Customer\RecentlyViewedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * RecentlyViewedController
 *
 * Endpoints for viewing and recording recently viewed products.
 *
 * @author Abdul Wadood
 */
class RecentlyViewedController extends Controller
{
    public function __construct(
        protected RecentlyViewedService $service
    ) {
        $this->middleware('auth:sanctum')->only(['index']);
    }

    public function index(Request $request)
    {
        $items = $this->service->listForUser($request->user());

        return RecentlyViewedResource::collection($items);
    }

    /**
     * Record a view event. Can be called from frontend silently.
     * For now requires auth; can be relaxed later with anon/session semantics.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ]);

        $this->service->recordView(
            $request->user(),
            $validated['product_id'],
            $validated['product_variant_id'] ?? null
        );

        return response()->json([], 204);
    }
}
