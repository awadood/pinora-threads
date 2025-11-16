<?php

namespace App\Repositories\Inventory\Contracts;

use App\Models\StockLevel;
use App\Repositories\IBaseRepository;

/**
 * IStockLevelRepository
 *
 * Repository contract for managing stock levels for each variant at each stock location.
 *
 * @author Abdul Wadood
 */
interface IStockLevelRepository extends IBaseRepository
{
    /**
     * Find a stock level row for the given stock and variant, if any.
     */
    public function findByStockAndVariant(int $stockId, int $variantId): ?StockLevel;
}
