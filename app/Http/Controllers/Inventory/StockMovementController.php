<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockMovementRequest;
use App\Http\Resources\Inventory\StockMovementResource;
use App\Models\StockMovement;
use App\Repositories\Inventory\Contracts\IStockMovementRepository;
use App\Services\Inventory\StockAdjustmentService;
use App\Support\QueryFilterable;
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
    use QueryFilterable;

    public function __construct(
        private readonly IStockMovementRepository $stockMovements,
        private readonly StockAdjustmentService $stockAdjustmentService,
    ) {
        $this->allowedFilters = [
            'stock_id',
            'variant_id',
            'stock_movement_type_code',
            'stock_batch_id',
            'performed_by',
            'created_at',
        ];
        $this->allowedSorts = ['stock_batch_id', 'stock_movement_type_code', 'created_at'];
    }

    public function index(Request $request)
    {
        $query = $this->applySorting(
            $this->applyFilters($this->stockMovements->query(), $request),
            $request
        );

        $perPage = $request->integer('per_page', 25);

        return StockMovementResource::collection(
            $query->with([
                'stock',
                'variant.attributes.option.attribute',
            ])->paginate($perPage)
        );
    }

    public function show(StockMovement $stockMovement)
    {
        return StockMovementResource::make($stockMovement);
    }

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

        return StockMovementResource::make($movement)->response()->setStatusCode(201);
    }

    /**
     * Movements are immutable. If you need to revert, create a compensating movement.
     */
    public function destroy(): JsonResponse
    {
        return response()->json([
            'message' => 'Stock movements are immutable. Create a compensating movement instead.',
        ], 405);
    }
}
