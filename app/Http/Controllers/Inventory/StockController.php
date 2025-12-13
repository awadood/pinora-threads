<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockRequest;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Stock;
use App\Repositories\Inventory\Contracts\IStockRepository;
use Illuminate\Http\JsonResponse;

/**
 * StockController
 *
 * Handles CRUD operations for stocks (logical warehouses / locations).
 *
 * @author Abdul Wadood
 */
class StockController extends Controller
{
    public function __construct(private IStockRepository $stocks) {}

    public function index()
    {
        $items = $this->stocks->all();

        return StockResource::collection($items);
    }

    public function show(Stock $stock)
    {
        return StockResource::make($stock);
    }

    public function store(StockRequest $request)
    {
        $entity = $this->stocks->create($request->validated());

        return StockResource::make($entity)
            ->response()
            ->setStatusCode(201);
    }

    public function update(StockRequest $request, Stock $stock)
    {
        $stock->update($request->validated());

        return StockResource::make($stock);
    }

    public function destroy(Stock $stock): JsonResponse
    {
        $this->stocks->disableIfNotDestroy($stock);

        return response()->json([], 204);
    }
}
