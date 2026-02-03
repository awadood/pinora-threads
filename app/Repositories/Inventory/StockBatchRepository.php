<?php

namespace App\Repositories\Inventory;

use App\Models\StockBatch;
use App\Repositories\BaseRepository;
use App\Repositories\Inventory\Contracts\IStockBatchRepository;

/**
 * StockBatchRepository
 *
 * Eloquent implementation for costed stock batches.
 *
 * @author Abdul Wadood
 */
class StockBatchRepository extends BaseRepository implements IStockBatchRepository
{
    protected string $modelClass = StockBatch::class;

    protected array $allowedSearchColumns = [
        'stock_id' => true,
        'product_id' => true,
        'currency_code' => true,
    ];
}
