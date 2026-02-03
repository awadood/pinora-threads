<?php

namespace App\Services\Inventory;

use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;
use App\Repositories\Inventory\Contracts\IStockMovementRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * StockAdjustmentService
 *
 * Encapsulates the logic for adjusting stock levels while recording immutable
 * stock movements and triggering back-in-stock notifications when appropriate.
 *
 * Concurrency-safe:
 *  - Uses SELECT ... FOR UPDATE on stock_levels rows to prevent lost updates.
 *  - Handles the race where the row doesn't exist yet (unique constraint).
 *
 * @author Abdul Wadood
 */
class StockAdjustmentService
{
    public function __construct(
        private readonly IStockLevelRepository $stockLevels,
        private readonly IStockMovementRepository $movements,
        private readonly BackInStockNotificationService $notificationService,
    ) {}

    /**
     * Adjust stock level and create a movement atomically (wraps its own transaction).
     *
     * @param  int  $quantityDelta  Positive for inbound, negative for outbound.
     * @param  array<string,mixed>  $meta
     */
    public function adjust(
        int $stockId,
        int $productId,
        int $quantityDelta,
        string $movementTypeCode,
        array $meta = [],
    ): StockMovement {
        return DB::transaction(function () use ($stockId, $productId, $quantityDelta, $movementTypeCode, $meta) {
            return $this->adjustInTransaction($stockId, $productId, $quantityDelta, $movementTypeCode, $meta);
        });
    }

    /**
     * Adjust stock level and create a movement (expects to be called within an existing transaction).
     *
     * @param  int  $quantityDelta  Positive for inbound, negative for outbound.
     * @param  array<string,mixed>  $meta
     */
    public function adjustInTransaction(
        int $stockId,
        int $productId,
        int $quantityDelta,
        string $movementTypeCode,
        array $meta = [],
    ): StockMovement {
        // Lock the existing level row (if present) to prevent concurrent lost updates.
        $level = $this->stockLevels->query()
            ->where('stock_id', $stockId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (! $level instanceof StockLevel) {
            // Row doesn't exist yet. Create it. In a concurrency race, another transaction
            // may create it first; handle unique constraint violation then re-fetch with lock.
            try {
                /** @var StockLevel $level */
                $level = $this->stockLevels->create([
                    'stock_id' => $stockId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'notify_below' => $meta['notify_below'] ?? 50,
                    'allow_backorder' => $meta['allow_backorder'] ?? false,
                    'promised_at' => $meta['promised_at'] ?? null,
                    'restock_eta' => $meta['restock_eta'] ?? null,
                ]);
            } catch (QueryException $e) {
                // SQLSTATE 23000 = integrity constraint violation (covers unique violation across drivers)
                if ((string) ($e->errorInfo[0] ?? '') !== '23000') {
                    throw $e;
                }

                // Someone else created the row; fetch it with lock.
                $level = $this->stockLevels->query()
                    ->where('stock_id', $stockId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (! $level instanceof StockLevel) {
                    // Extremely unlikely; fail loudly rather than proceeding incorrectly.
                    throw $e;
                }
            }
        }

        $wasOutOfStock = $level->quantity <= 0;

        // Enforce non-negative final quantity. (You already store quantity unsigned.)
        $level->quantity = max(0, (int) $level->quantity + $quantityDelta);
        $level->save();

        /** @var StockMovement $movement */
        $movement = $this->movements->create([
            'stock_id' => $stockId,
            'product_id' => $productId,
            'stock_movement_type_code' => $movementTypeCode,
            'quantity_delta' => $quantityDelta,
            'stock_batch_id' => $meta['stock_batch_id'] ?? null,
            'order_id' => $meta['order_id'] ?? null,
            'performed_by' => $meta['performed_by'] ?? null,
            'reason' => $meta['reason'] ?? null,
        ]);

        if ($wasOutOfStock && $level->quantity > 0) {
            $this->notificationService->notifyAll($productId);
        }

        return $movement;
    }
}
