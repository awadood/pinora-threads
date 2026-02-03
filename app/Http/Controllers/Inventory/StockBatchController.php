<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockBatchRequest;
use App\Http\Resources\Inventory\StockBatchResource;
use App\Models\StockBatch;
use App\Repositories\Inventory\Contracts\IStockBatchRepository;
use App\Services\Inventory\StockBatchService;
use App\Support\QueryFilterable;
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
    use QueryFilterable;

    public function __construct(
        private readonly IStockBatchRepository $stockBatches,
        private readonly StockBatchService $stockBatchService,
    ) {
        $this->allowedFilters = ['stock_id', 'product_id', 'received_at', 'qty_remaining'];
        $this->likeFilters = [];
        $this->allowedSorts = ['product_id', 'received_at', 'qty_remaining'];
    }

    public function index(Request $request)
    {
        $query = $this->applyFilters($this->stockBatches->query(), $request);
        $query = $this->applySorting($query, $request);

        $perPage = $request->integer('per_page', 25);

        return StockBatchResource::collection(
            $query->with([
                'stock',
                'product.attributes.option.attribute',
                'product.thumbnailMedia.asset.renditions',
            ])->paginate($perPage)
        );
    }

    public function show(int $stock_batch)
    {
        $entity = $this->stockBatches->find($stock_batch);
        abort_if(! $entity, 404);

        return StockBatchResource::make($entity);
    }

    public function store(StockBatchRequest $request)
    {
        $data = $request->validated();
        $performedBy = $request->user()->id;

        $entity = $this->stockBatchService->receive($data, $performedBy);

        return StockBatchResource::make($entity)->response()->setStatusCode(201);
    }

    public function update(StockBatchRequest $request, StockBatch $stockBatch)
    {
        $stockBatch->update($request->validated());

        return StockBatchResource::make($stockBatch);
    }

    public function destroy(StockBatch $stockBatch): JsonResponse
    {
        $this->stockBatches->disableIfNotDestroy($stockBatch);

        return response()->json([], 204);
    }
}
