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

    /**
     * calculates the total number of product variants in the stock
     *
     * @param  int  $stockId
     * @return int the total variants in the stock
     */
    public function getVariantCount(int $stockId): int;
    
    /**
     * calculates the total quantity of product variants in the stock
     *
     * @param  int  $stockId
     * @return int the total qauntity of product variants in the stock
     */
    public function getTotalQuantity(int $stockId): int;
}
