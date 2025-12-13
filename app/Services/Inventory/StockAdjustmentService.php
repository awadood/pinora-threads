<?php

namespace App\Services\Inventory;

use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;
use App\Repositories\Inventory\Contracts\IStockMovementRepository;
use Illuminate\Support\Facades\DB;

/**
 * StockAdjustmentService
 *
 * Encapsulates the logic for adjusting stock levels while recording immutable
 * stock movements and triggering back-in-stock notifications when appropriate.
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
     * Adjust stock level and create a movement atomically.
     *
     * @param  int  $quantityDelta  Positive for inbound, negative for outbound.
     * @param  array<string,mixed>  $meta
     */
    public function adjust(
        int $stockId,
        int $variantId,
        int $quantityDelta,
        string $movementTypeCode,
        array $meta = [],
    ): StockMovement {
        return DB::transaction(function () use ($stockId, $variantId, $quantityDelta, $movementTypeCode, $meta) {
            $level = $this->stockLevels->findByStockAndVariant($stockId, $variantId);

            if (! $level instanceof StockLevel) {
                /** @var StockLevel $level */
                $level = $this->stockLevels->create([
                    'stock_id' => $stockId,
                    'variant_id' => $variantId,
                    'quantity' => 0,
                    'notify_below' => $meta['notify_below'] ?? 50,
                    'allow_backorder' => $meta['allow_backorder'] ?? false,
                    'promised_at' => $meta['promised_at'] ?? null,
                    'restock_eta' => $meta['restock_eta'] ?? null,
                ]);
            }

            $wasOutOfStock = $level->quantity <= 0;

            $level->quantity = max(0, $level->quantity + $quantityDelta);
            $level->save();

            /** @var StockMovement $movement */
            $movement = $this->movements->create([
                'stock_id' => $stockId,
                'variant_id' => $variantId,
                'stock_movement_type_code' => $movementTypeCode,
                'quantity_delta' => $quantityDelta,
                'stock_batch_id' => $meta['stock_batch_id'] ?? null,
                'order_id' => $meta['order_id'] ?? null,
                'performed_by' => $meta['performed_by'] ?? null,
                'reason' => $meta['reason'] ?? null,
            ]);

            if ($wasOutOfStock && $level->quantity > 0) {
                $this->notificationService->notifyAll($variantId);
            }

            return $movement;
        });
    }
}
