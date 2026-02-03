<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Stocks
            $pkStockId = $this->upsertStock('PK_RWP_RB_1', 'Rawalpindi Commercial', 'Punjab', 'PK', 1);
            $usStockId = $this->upsertStock('US_CA_MAIN', 'San Jose Main', 'California', 'US', 1);

            // Check currencies exist (FK in stock_batches)
            $currencyPk = DB::table('currencies')->where('code', 'PKR')->value('code');
            $currencyUs = DB::table('currencies')->where('code', 'USD')->value('code');

            if (! $currencyPk && ! $currencyUs) {
                throw new \RuntimeException('InventorySeeder: currencies table lacks PKR/USD. Seed currencies first.');
            }

            // Stock movement type: your table stock_movement_types must contain a usable code
            // We pick first available to avoid FK failure.
            $movementTypeCode = DB::table('stock_movement_types')->value('code'); // first row
            // If not present, we will SKIP stock_movements to avoid FK failures.
            $canWriteMovements = (bool) $movementTypeCode;

            // For each product, create stock levels (both stocks), and initial batches.
            $products = DB::table('products')->select('id')->get();

            foreach ($products as $p) {
                $productId = (int) $p->id;

                // Levels
                $this->upsertStockLevel($pkStockId, $productId, rand(0, 250), 50, false);
                $this->upsertStockLevel($usStockId, $productId, rand(0, 250), 50, false);

                // Batches (one per stock, if currency exists)
                if ($currencyPk) {
                    $batchIdPk = $this->insertBatch($pkStockId, $productId, 'PKR', rand(2500, 12000), rand(30, 200));
                    if ($canWriteMovements) {
                        $this->insertMovement($pkStockId, $productId, $movementTypeCode, +1 * DB::table('stock_batches')->where('id', $batchIdPk)->value('qty_received'), $batchIdPk);
                    }
                }

                if ($currencyUs) {
                    $batchIdUs = $this->insertBatch($usStockId, $productId, 'USD', rand(10, 80), rand(30, 200));
                    if ($canWriteMovements) {
                        $this->insertMovement($usStockId, $productId, $movementTypeCode, +1 * DB::table('stock_batches')->where('id', $batchIdUs)->value('qty_received'), $batchIdUs);
                    }
                }
            }
        });
    }

    private function upsertStock(string $code, string $title, string $region, string $countryCode, int $priority): int
    {
        $id = DB::table('stocks')->where('title', $title)->value('id');
        if ($id) {
            return (int) $id;
        }

        return DB::table('stocks')->insertGetId([
            'code' => $code,
            'title' => $title,
            'region' => $region,
            'country_code' => $countryCode,
            'priority' => $priority,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertStockLevel(int $stockId, int $productId, int $qty, int $notifyBelow, bool $allowBackorder): void
    {
        DB::table('stock_levels')->updateOrInsert(
            ['stock_id' => $stockId, 'product_id' => $productId],
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

    private function insertBatch(int $stockId, int $productId, string $currencyCode, float $unitCost, int $qty): int
    {
        $receivedAt = Carbon::now()->subDays(rand(1, 120))->toDateString();

        return DB::table('stock_batches')->insertGetId([
            'stock_id' => $stockId,
            'product_id' => $productId,
            'received_at' => $receivedAt,
            'currency_code' => $currencyCode,
            'unit_cost' => number_format($unitCost, 2, '.', ''),
            'qty_received' => $qty,
            'qty_remaining' => $qty,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertMovement(int $stockId, int $productId, string $typeCode, int $delta, int $batchId): void
    {
        DB::table('stock_movements')->insert([
            'stock_id' => $stockId,
            'product_id' => $productId,
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
