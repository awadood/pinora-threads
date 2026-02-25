<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\WishlistItemResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Services\Customer\WishlistItemService;
use App\Support\Storefront\StoreContext;
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

        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);
        $items = $this->service->listForWishlist($wishlist, $ctx->currency);

        return WishlistItemResource::collection($items);
    }

    public function store(Request $request, Wishlist $wishlist): WishlistItemResource
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $item = $this->service->addToWishlist(
            $request->user(),
            $wishlist,
            $validated['product_id']
        );

        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);
        $item->load([
            'product' => function ($productQuery) use ($ctx) {
                $productQuery
                    ->where('active', true)
                    ->withCount('variants as variants_count')
                    ->with([
                        'prices' => fn ($priceQuery) => $priceQuery->where('currency_code', $ctx->currency),
                        'thumbnailMedia.asset.renditions',
                    ]);
            },
        ]);

        return WishlistItemResource::make($item);
    }

    public function destroy(Request $request, WishlistItem $item): JsonResponse
    {
        $this->service->removeFromWishlist($request->user(), $item);

        return response()->json([], 204);
    }
}
