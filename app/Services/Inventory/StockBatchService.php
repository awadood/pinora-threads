<?php

namespace App\Services\Inventory;

use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\StockMovementType;
use App\Repositories\Inventory\Contracts\IStockBatchRepository;
use Illuminate\Support\Facades\DB;

/**
 * StockBatchService
 *
 * Orchestrates stock batch receiving:
 *  - Creates stock_batches row
 *  - Creates immutable stock_movements (+qty) linked to the batch
 *  - Increases stock_levels.quantity (with locking via StockAdjustmentService)
 *
 * @author Abdul Wadood
 */
class StockBatchService
{
    public function __construct(
        private readonly IStockBatchRepository $stockBatches,
        private readonly StockAdjustmentService $stockAdjustmentService,
    ) {}

    /**
     * Receive (add) a new stock batch and apply its quantity to stock levels.
     *
     * @param  array<string,mixed>  $data  Validated payload from StockBatchRequest
     */
    public function receive(array $data, int $performedBy): StockBatch
    {
        return DB::transaction(function () use ($data, $performedBy) {
            /** @var StockBatch $batch */
            $batch = $this->stockBatches->create([
                'stock_id' => $data['stock_id'],
                'variant_id' => $data['variant_id'],
                'received_at' => $data['received_at'],
                'currency_code' => $data['currency_code'],
                'unit_cost' => $data['unit_cost'],
                'qty_received' => $data['qty_received'],
                'qty_remaining' => $data['qty_received'],
            ]);

            // Apply the receipt into stock_levels and create stock_movements row (+qty)
            // inside the same transaction (no nested transaction).
            $this->stockAdjustmentService->adjustInTransaction(
                stockId: (int) $batch->stock_id,
                variantId: (int) $batch->variant_id,
                quantityDelta: (int) $batch->qty_received,
                movementTypeCode: StockMovementType::PURCHASE,
                meta: [
                    'stock_batch_id' => (int) $batch->id,
                    'performed_by' => $performedBy,
                    'reason' => __('inventory.new_batch'),
                ],
            );

            return $batch->fresh();
        });
    }
}
