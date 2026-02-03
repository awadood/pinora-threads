<?php

namespace App\Repositories\Inventory\Contracts;

use App\Models\StockLevel;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Builder;

/**
 * IStockLevelRepository
 *
 * Repository contract for managing stock levels for each product at each stock location.
 *
 * @author Abdul Wadood
 */
interface IStockLevelRepository extends IBaseRepository
{
    /**
     * Find a stock level row for the given stock and product, if any.
     */
    public function findByStockAndProduct(int $stockId, int $productId): ?StockLevel;

    /**
     * Apply business status filtering for stock levels.
     *
     * Allowed values: all, in_stock, low_stock, out_of_stock, backorder
     */
    public function applyStatusFilter(Builder $query, ?string $status): Builder;
}
