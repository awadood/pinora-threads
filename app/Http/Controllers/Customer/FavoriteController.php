<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\FavoriteResource;
use App\Services\Customer\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * FavoriteController
 *
 * Endpoints for listing and toggling favorites for the current customer.
 *
 * @author Abdul Wadood
 */
class FavoriteController extends Controller
{
    public function __construct(
        protected FavoriteService $service
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $items = $this->service->listForUser($request->user());

        return FavoriteResource::collection($items);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ]);

        $isFavorite = $this->service->toggle(
            $request->user(),
            $validated['product_id'],
            $validated['product_variant_id'] ?? null
        );

        return response()->json(['favorite' => $isFavorite]);
    }
}
