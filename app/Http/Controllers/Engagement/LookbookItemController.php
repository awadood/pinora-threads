<?php

namespace App\Http\Controllers\Engagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\LookbookItemRequest;
use App\Http\Resources\Engagement\LookbookItemResource;
use App\Models\Lookbook;
use App\Models\LookbookItem;
use App\Repositories\Engagement\Contracts\ILookbookItemRepository;
use Illuminate\Http\JsonResponse;

/**
 * LookbookItemController
 *
 * Manage styled looks (items) within a lookbook.
 *
 * @author Abdul Wadood
 */
class LookbookItemController extends Controller
{
    public function __construct(
        protected ILookbookItemRepository $items
    ) {}

    /**
     * Admin-only: list items for a given lookbook by model binding.
     */
    public function indexByLookbook(Lookbook $lookbook)
    {
        $items = $this->items->getByLookbook($lookbook->getKey());

        return LookbookItemResource::collection($items);
    }

    public function show(LookbookItem $item)
    {
        return new LookbookItemResource($item);
    }

    public function store(LookbookItemRequest $request, Lookbook $lookbook)
    {
        $data = $request->validated();
        $data['lookbook_id'] = $lookbook->getKey();

        $item = $this->items->create($data);

        return (new LookbookItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function update(LookbookItemRequest $request, LookbookItem $item)
    {
        $item->fill($request->validated());
        $item->save();

        return new LookbookItemResource($item);
    }

    public function destroy(LookbookItem $item): JsonResponse
    {
        $item->delete();

        return response()->json([], 204);
    }
}
