<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockLevelRequest;
use App\Http\Resources\Inventory\StockLevelResource;
use App\Http\Resources\Inventory\StockMovementResource;
use App\Models\StockLevel;
use App\Models\StockMovementType;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;
use App\Services\Inventory\StockAdjustmentService;
use App\Support\QueryFilterable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StockLevelController
 *
 * CRUD + listing for stock levels per product per stock location.
 *
 * @author Abdul Wadood
 */
class StockLevelController extends Controller
{
    use QueryFilterable;

    public function __construct(
        private IStockLevelRepository $stockLevels,
        private StockAdjustmentService $adjustmentService
    ) {
        $this->allowedFilters = ['stock_id', 'product_id', 'quantity', 'notify_below', 'allow_backorder', 'updated_at'];
        $this->allowedSorts = ['quantity', 'updated_at', 'product_id', 'stock_id', 'notify_below'];
    }

    public function index(Request $request)
    {
        $query = $this->stockLevels->query()->with([
            'stock',
            'product.attributes.option.attribute',
            'product.thumbnailMedia.asset.renditions',
        ]);

        $query = $this->stockLevels->applyStatusFilter($query, data_get($request->query('filter', []), 'status'));
        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request);

        $perPage = (int) $request->integer('per_page', 50);

        return StockLevelResource::collection($query->paginate($perPage));
    }

    public function show(StockLevel $stockLevel)
    {
        return StockLevelResource::make($stockLevel);
    }

    public function store(StockLevelRequest $request)
    {
        $entity = $this->stockLevels->create($request->validated());

        return StockLevelResource::make($entity)->response()->setStatusCode(201);
    }

    public function update(StockLevelRequest $request, StockLevel $stockLevel)
    {
        $stockLevel->update($request->validated());

        return StockLevelResource::make($stockLevel);
    }

    public function destroy(StockLevel $stockLevel): JsonResponse
    {
        $this->stockLevels->disableIfNotDestroy($stockLevel);

        return response()->json([], 204);
    }

    public function adjust(Request $request, StockLevel $stockLevel)
    {
        $data = $request->validate([
            'adjustment_type' => ['required', 'in:increase,decrease'],
            'quantity' => ['required', 'integer', 'min:1'],
            'stock_batch_id' => ['nullable', 'integer', 'exists:stock_batches,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $meta = [
            'stock_batch_id' => $data['stock_batch_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'performed_by' => $request->user()->id,
            'reason' => $data['reason'] ?? null,
        ];

        // Convert adjustment_type + quantity to a signed delta
        $quantityDelta = $data['adjustment_type'] === 'increase' ? $data['quantity'] : -$data['quantity'];

        // Delegate to domain service (handles level create/update, movement, notifications)
        $movement = $this->adjustmentService->adjust(
            $stockLevel->stock_id, $stockLevel->product_id, $quantityDelta, StockMovementType::ADJUSTMENT, $meta);

        $stockLevel->refresh(); // Refresh the stock level to get the new quantity

        return response()->json([
            'stock_level' => StockLevelResource::make($stockLevel),
            'movement' => StockMovementResource::make($movement),
        ]);
    }
}
