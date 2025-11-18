<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockBatchRequest;
use App\Http\Resources\Inventory\StockBatchResource;
use App\Repositories\Inventory\Contracts\IStockBatchRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StockBatchController
 *
 * CRUD for costed purchase batches used for inventory valuation (FIFO/average) and audits.
 *
 * @author Abdul Wadood
 */
class StockBatchController extends Controller
{
    public function __construct(private readonly IStockBatchRepository $stockBatches) {}

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
            ? $this->stockBatches->all()
            : $this->stockBatches->search($criteria);

        return StockBatchResource::collection($items);
    }

    public function show(int $stock_batch)
    {
        $entity = $this->stockBatches->find($stock_batch);
        abort_if(! $entity, 404);

        return StockBatchResource::make($entity);
    }

    public function store(StockBatchRequest $request)
    {
        $entity = $this->stockBatches->create($request->validated());

        return (StockBatchResource::make($entity))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StockBatchRequest $request, int $stock_batch)
    {
        $entity = $this->stockBatches->find($stock_batch);
        abort_if(! $entity, 404);

        $entity->fill($request->validated())->save();

        return StockBatchResource::make($entity);
    }

    public function destroy(int $stock_batch): JsonResponse
    {
        $entity = $this->stockBatches->find($stock_batch);
        abort_if(! $entity, 404);

        $this->stockBatches->disableIfNotDestroy($entity);

        return response()->json([], 204);
    }
}
