<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Stocks
            $pkStockId = $this->upsertStock('PK Main');
            $usStockId = $this->upsertStock('US NJ');

            // Check currencies exist (FK in stock_batches)
            $currencyPk = DB::table('currencies')->where('code', 'PKR')->value('code');
            $currencyUs = DB::table('currencies')->where('code', 'USD')->value('code');

            if (!$currencyPk && !$currencyUs) {
                throw new \RuntimeException("InventorySeeder: currencies table lacks PKR/USD. Seed currencies first.");
            }

            // Stock movement type: your table stock_movement_types must contain a usable code
            // We pick first available to avoid FK failure.
            $movementTypeCode = DB::table('stock_movement_types')->value('code'); // first row
            // If not present, we will SKIP stock_movements to avoid FK failures.
            $canWriteMovements = (bool)$movementTypeCode;

            // For each variant, create stock levels (both stocks), and initial batches.
            $variants = DB::table('product_variants')->select('id')->get();

            foreach ($variants as $v) {
                $variantId = (int)$v->id;

                // Levels
                $this->upsertStockLevel($pkStockId, $variantId, rand(0, 250), 50, false);
                $this->upsertStockLevel($usStockId, $variantId, rand(0, 250), 50, false);

                // Batches (one per stock, if currency exists)
                if ($currencyPk) {
                    $batchIdPk = $this->insertBatch($pkStockId, $variantId, 'PKR', rand(2500, 12000), rand(30, 200));
                    if ($canWriteMovements) {
                        $this->insertMovement($pkStockId, $variantId, $movementTypeCode, +1 * DB::table('stock_batches')->where('id', $batchIdPk)->value('qty_received'), $batchIdPk);
                    }
                }

                if ($currencyUs) {
                    $batchIdUs = $this->insertBatch($usStockId, $variantId, 'USD', rand(10, 80), rand(30, 200));
                    if ($canWriteMovements) {
                        $this->insertMovement($usStockId, $variantId, $movementTypeCode, +1 * DB::table('stock_batches')->where('id', $batchIdUs)->value('qty_received'), $batchIdUs);
                    }
                }
            }
        });
    }

    private function upsertStock(string $title): int
    {
        $id = DB::table('stocks')->where('title', $title)->value('id');
        if ($id) return (int)$id;

        return DB::table('stocks')->insertGetId([
            'title' => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertStockLevel(int $stockId, int $variantId, int $qty, int $notifyBelow, bool $allowBackorder): void
    {
        DB::table('stock_levels')->updateOrInsert(
            ['stock_id' => $stockId, 'variant_id' => $variantId],
            [
                'quantity' => $qty,
                'notify_below' => $notifyBelow,
                'allow_backorder' => $allowBackorder,
                'promised_at' => null,
                'restock_eta' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function insertBatch(int $stockId, int $variantId, string $currencyCode, float $unitCost, int $qty): int
    {
        $receivedAt = Carbon::now()->subDays(rand(1, 120))->toDateString();

        return DB::table('stock_batches')->insertGetId([
            'stock_id' => $stockId,
            'variant_id' => $variantId,
            'received_at' => $receivedAt,
            'currency_code' => $currencyCode,
            'unit_cost' => number_format($unitCost, 2, '.', ''),
            'qty_received' => $qty,
            'qty_remaining' => $qty,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertMovement(int $stockId, int $variantId, string $typeCode, int $delta, int $batchId): void
    {
        DB::table('stock_movements')->insert([
            'stock_id' => $stockId,
            'variant_id' => $variantId,
            'stock_movement_type_code' => $typeCode,
            'quantity_delta' => $delta,
            'stock_batch_id' => $batchId,
            'order_id' => null,
            'performed_by' => null,
            'reason' => 'Seed opening stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
