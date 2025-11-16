<?php

namespace App\Http\Controllers\Engagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\LookbookRequest;
use App\Http\Resources\Engagement\LookbookItemResource;
use App\Http\Resources\Engagement\LookbookResource;
use App\Models\Lookbook;
use App\Services\Engagement\LookbookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * LookbookController
 *
 * Campaign / editorial lookbooks shown on homepage and landing pages.
 *
 * @author Abdul Wadood
 */
class LookbookController extends Controller
{
    public function __construct(
        protected LookbookService $service
    ) {}

    /**
     * Public endpoint — list active & published lookbooks.
     */
    public function index(Request $request)
    {
        $items = $this->service->getPublicLookbooks();

        return LookbookResource::collection($items);
    }

    /**
     * Public endpoint — show a lookbook by slug.
     */
    public function showBySlug(string $slug)
    {
        $lookbook = $this->service->findBySlugOrFail($slug);

        return new LookbookResource($lookbook);
    }

    /**
     * Public endpoint — list items for a lookbook slug.
     */
    public function items(string $slug)
    {
        $items = $this->service->getItemsByLookbookSlug($slug);

        return LookbookItemResource::collection($items);
    }

    /**
     * Admin-only create.
     */
    public function store(LookbookRequest $request)
    {
        $lookbook = $this->service->create($request->validated());

        return (new LookbookResource($lookbook))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Admin-only update.
     */
    public function update(LookbookRequest $request, Lookbook $lookbook)
    {
        $updated = $this->service->update($lookbook, $request->validated());

        return new LookbookResource($updated);
    }

    /**
     * Admin-only delete.
     */
    public function destroy(Lookbook $lookbook): JsonResponse
    {
        $this->service->delete($lookbook);

        return response()->json([], 204);
    }
}
