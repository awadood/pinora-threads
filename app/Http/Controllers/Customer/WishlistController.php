<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\WishlistResource;
use App\Models\Wishlist;
use App\Services\Customer\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * WishlistController
 *
 * Endpoints for managing user wishlists and public share views.
 *
 * @author Abdul Wadood
 */
class WishlistController extends Controller
{
    public function __construct(protected WishlistService $service) {}

    public function index(Request $request)
    {
        $lists = $this->service->listForUser($request->user());

        return WishlistResource::collection($lists);
    }

    public function store(Request $request): WishlistResource
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'public' => ['sometimes', 'boolean'],
        ]);

        $wishlist = $this->service->createForUser($request->user(), $validated);

        return WishlistResource::make($wishlist);
    }

    public function show(Request $request, Wishlist $wishlist): WishlistResource
    {
        if ($wishlist->user_id !== $request->user()->getAuthIdentifier()) {
            abort(403);
        }

        return WishlistResource::make($wishlist);
    }

    public function update(Request $request, Wishlist $wishlist): WishlistResource
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'public' => ['sometimes', 'boolean'],
        ]);

        $wishlist = $this->service->updateForUser($request->user(), $wishlist, $validated);

        return WishlistResource::make($wishlist);
    }

    public function destroy(Request $request, Wishlist $wishlist): JsonResponse
    {
        $this->service->deleteForUser($request->user(), $wishlist);

        return response()->json([], 204);
    }

    /**
     * Public view via share token (no auth).
     */
    public function showShared(string $token): ?WishlistResource
    {
        $wishlist = $this->service->findShared($token);
        if (! $wishlist || ! $wishlist->public) {
            abort(404);
        }

        return WishlistResource::make($wishlist);
    }
}
