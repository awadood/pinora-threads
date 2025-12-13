<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockRequest;
use App\Http\Resources\Inventory\StockResource;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;
use App\Repositories\Inventory\Contracts\IStockRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StockController
 *
 * Handles CRUD operations for stocks (logical warehouses / locations).
 *
 * @author Abdul Wadood
 */
class StockController extends Controller
{
    public function __construct(private IStockRepository $stocks, private IStockLevelRepository $stockLevels) {}

    public function index(Request $request)
    {
        $items = $this->stocks->all();

        $items->each(function ($stock) {
            $stock->total_variants = $this->stockLevels->getVariantCount($stock->id);
            $stock->total_quantity = $this->stockLevels->getTotalQuantity($stock->id);
        });

        return StockResource::collection($items);
    }

    public function show(int $stock)
    {
        $entity = $this->stocks->find($stock);
        abort_if(! $entity, 404);

        return StockResource::make($entity);
    }

    public function store(StockRequest $request)
    {
        $entity = $this->stocks->create($request->validated());

        return StockResource::make($entity)
            ->response()
            ->setStatusCode(201);
    }

    public function update(StockRequest $request, int $stock)
    {
        $entity = $this->stocks->find($stock);
        abort_if(! $entity, 404);

        $entity->fill($request->validated())->save();

        return StockResource::make($entity);
    }

    public function destroy(int $stock): JsonResponse
    {
        $entity = $this->stocks->find($stock);
        abort_if(! $entity, 404);

        $this->stocks->disableIfNotDestroy($entity);

        return response()->json([], 204);
    }
}
