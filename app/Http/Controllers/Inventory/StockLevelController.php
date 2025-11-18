<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockLevelRequest;
use App\Http\Resources\Inventory\StockLevelResource;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StockLevelController
 *
 * CRUD + listing for stock levels per variant per stock location.
 *
 * @author Abdul Wadood
 */
class StockLevelController extends Controller
{
    public function __construct(
        private readonly IStockLevelRepository $stockLevels,
    ) {}

    public function index(Request $request)
    {
        $criteria = [];

        if ($request->filled('stock_id')) {
            $criteria[] = ['col' => 'stock_id', 'op' => '=', 'value' => (int) $request->query('stock_id')];
        }

        if ($request->filled('variant_id')) {
            $criteria[] = ['col' => 'variant_id', 'op' => '=', 'value' => (int) $request->query('variant_id')];
        }

        $items = empty($criteria)
            ? $this->stockLevels->all()
            : $this->stockLevels->search($criteria);

        return StockLevelResource::collection($items);
    }

    public function show(int $stock_level)
    {
        $entity = $this->stockLevels->find($stock_level);
        abort_if(! $entity, 404);

        return StockLevelResource::make($entity);
    }

    public function store(StockLevelRequest $request)
    {
        $entity = $this->stockLevels->create($request->validated());

        return StockLevelResource::make($entity)
            ->response()
            ->setStatusCode(201);
    }

    public function update(StockLevelRequest $request, int $stock_level)
    {
        $entity = $this->stockLevels->find($stock_level);
        abort_if(! $entity, 404);

        $entity->fill($request->validated())->save();

        return StockLevelResource::make($entity);
    }

    public function destroy(int $stock_level): JsonResponse
    {
        $entity = $this->stockLevels->find($stock_level);
        abort_if(! $entity, 404);

        $this->stockLevels->disableIfNotDestroy($entity);

        return response()->json([], 204);
    }
}
