<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Requests\Catalog\CollectionProductRequest;
use App\Repositories\Catalog\Contracts\ICollectionProductRepository;
use Illuminate\Routing\Controller;

/**
 * CollectionProductController
 *
 * Manage collection <-> product pivot table.
 *
 * @author Abdul Wadood
 */
class CollectionProductController extends Controller
{
    protected ICollectionProductRepository $pivots;

    public function __construct(ICollectionProductRepository $pivots)
    {
        $this->pivots = $pivots;
    }

    public function store(CollectionProductRequest $request)
    {
        $pivot = $this->pivots->create($request->validated());

        return response()->json($pivot, 201);
    }

    public function destroy(int $collection, int $product)
    {
        $record = $this->pivots->query()
            ->where('collection_id', $collection)
            ->where('product_id', $product)
            ->firstOrFail();

        $this->pivots->destroy($record->getKey());

        return response()->json([], 204);
    }
}
