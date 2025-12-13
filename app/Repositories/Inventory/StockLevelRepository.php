<?php

namespace App\Repositories\Inventory;

use App\Models\StockLevel;
use App\Repositories\BaseRepository;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;

/**
 * StockLevelRepository
 *
 * Eloquent implementation for stock levels.
 *
 * @author Abdul Wadood
 */
class StockLevelRepository extends BaseRepository implements IStockLevelRepository
{
    protected string $modelClass = StockLevel::class;

    protected array $allowedSearchColumns = [
        'stock_id' => true,
        'variant_id' => true,
    ];

    public function findByStockAndVariant(int $stockId, int $variantId): ?StockLevel
    {
        return $this->query()
            ->where('stock_id', $stockId)
            ->where('variant_id', $variantId)
            ->first();
    }

    public function getVariantCount(int $stockId): int
    {
        return $this->query()->where('stock_id', $stockId)->count();
    }

    public function getTotalQuantity(int $stockId): int
    {
        return $this->query()->where('stock_id', $stockId)->sum('quantity');
    }
}
