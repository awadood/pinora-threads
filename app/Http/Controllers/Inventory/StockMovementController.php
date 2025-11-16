<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockMovementRequest;
use App\Http\Resources\Inventory\StockMovementResource;
use App\Repositories\Inventory\Contracts\IStockMovementRepository;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StockMovementController
 *
 * Read and create immutable stock movements. Updates are not allowed.
 *
 * @author Abdul Wadood
 */
class StockMovementController extends Controller
{
    public function __construct(
        private readonly IStockMovementRepository $stockMovements,
        private readonly StockAdjustmentService $stockAdjustmentService,
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
            ? $this->stockMovements->all()
            : $this->stockMovements->search($criteria);

        return StockMovementResource::collection($items);
    }

    public function show(int $stock_movement)
    {
        $entity = $this->stockMovements->find($stock_movement);
        abort_if(! $entity, 404);

        return new StockMovementResource($entity);
    }

    /**
     * Creates a new stock movement and adjusts the stock level atomically.
     */
    public function store(StockMovementRequest $request)
    {
        $data = $request->validated();

        $movement = $this->stockAdjustmentService->adjust(
            stockId: $data['stock_id'],
            variantId: $data['variant_id'],
            quantityDelta: $data['quantity_delta'],
            movementTypeCode: $data['stock_movement_type_code'],
            meta: [
                'reason' => $data['reason'] ?? null,
                'stock_batch_id' => $data['stock_batch_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'performed_by' => $data['performed_by'] ?? null,
            ]
        );

        return (new StockMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Movements are immutable. If you need to revert, create a compensating movement.
     */
    public function destroy(int $stock_movement): JsonResponse
    {
        return response()->json([
            'message' => 'Stock movements are immutable. Create a compensating movement instead.',
        ], 405);
    }
}
