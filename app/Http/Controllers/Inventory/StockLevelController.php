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
        private IStockLevelRepository $repository,
        private StockAdjustmentService $adjustmentService
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

        $items = $this->repository->search($criteria, ['stock', 'variant']);

        return StockLevelResource::collection($items);
    }

    public function show(StockLevel $stockLevel)
    {
        return StockLevelResource::make($stockLevel);
    }

    public function store(StockLevelRequest $request)
    {
        $entity = $this->repository->create($request->validated());

        return StockLevelResource::make($entity)->response()->setStatusCode(201);
    }

    public function update(StockLevelRequest $request, StockLevel $stockLevel)
    {
        $stockLevel->update($request->validated());

        return StockLevelResource::make($stockLevel);
    }

    public function destroy(StockLevel $stockLevel): JsonResponse
    {
        $this->repository->disableIfNotDestroy($stockLevel);

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
            $stockLevel->stock_id, $stockLevel->variant_id, $quantityDelta, StockMovementType::ADJUSTMENT, $meta);

        $stockLevel->refresh(); // Refresh the stock level to get the new quantity

        return response()->json([
            'stock_level' => StockLevelResource::make($stockLevel),
            'movement' => StockMovementResource::make($movement),
        ]);
    }
}
