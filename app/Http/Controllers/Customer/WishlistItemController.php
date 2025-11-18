<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\WishlistItemResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Services\Customer\WishlistItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * WishlistItemController
 *
 * Endpoints for adding/removing items from a wishlist.
 *
 * @author Abdul Wadood
 */
class WishlistItemController extends Controller
{
    public function __construct(protected WishlistItemService $service) {}

    public function index(Request $request, Wishlist $wishlist)
    {
        if ($wishlist->user_id !== $request->user()->getAuthIdentifier()) {
            abort(403);
        }

        $items = $this->service->listForWishlist($wishlist);

        return WishlistItemResource::collection($items);
    }

    public function store(Request $request, Wishlist $wishlist): WishlistItemResource
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ]);

        $item = $this->service->addToWishlist(
            $request->user(),
            $wishlist,
            $validated['product_id'],
            $validated['product_variant_id'] ?? null
        );

        return WishlistItemResource::make($item);
    }

    public function destroy(Request $request, WishlistItem $item): JsonResponse
    {
        $this->service->removeFromWishlist($request->user(), $item);

        return response()->json([], 204);
    }
}
