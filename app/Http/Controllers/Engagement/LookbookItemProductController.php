<?php

namespace App\Http\Controllers\Engagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\LookbookItemProductRequest;
use App\Http\Resources\Engagement\LookbookItemProductResource;
use App\Models\LookbookItem;
use App\Models\LookbookItemProduct;
use App\Repositories\Engagement\Contracts\ILookbookItemProductRepository;
use Illuminate\Http\JsonResponse;

/**
 * LookbookItemProductController
 *
 * Attach products / variants to a styled look.
 *
 * @author Abdul Wadood
 */
class LookbookItemProductController extends Controller
{
    public function __construct(
        protected ILookbookItemProductRepository $attachments
    ) {}

    /**
     * Public endpoint — list attached products for a lookbook item.
     */
    public function index(LookbookItem $item)
    {
        $rows = $this->attachments->getByItem($item->getKey());

        return LookbookItemProductResource::collection($rows);
    }

    public function store(LookbookItemProductRequest $request, LookbookItem $item)
    {
        $data = $request->validated();
        $data['lookbook_item_id'] = $item->getKey();

        $row = $this->attachments->create($data);

        return (new LookbookItemProductResource($row))
            ->response()
            ->setStatusCode(201);
    }

    public function update(LookbookItemProductRequest $request, LookbookItemProduct $attachment)
    {
        $attachment->fill($request->validated());
        $attachment->save();

        return new LookbookItemProductResource($attachment);
    }

    public function destroy(LookbookItemProduct $attachment): JsonResponse
    {
        $attachment->delete();

        return response()->json([], 204);
    }
}
