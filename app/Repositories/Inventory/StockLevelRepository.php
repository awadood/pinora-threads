<?php

namespace App\Repositories\Inventory;

use App\Models\StockLevel;
use App\Repositories\BaseRepository;
use App\Repositories\Inventory\Contracts\IStockLevelRepository;
use Illuminate\Database\Eloquent\Builder;

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

    public function applyStatusFilter(Builder $query, ?string $status): Builder
    {
        $status = $status ? strtolower(trim($status)) : null;

        return match ($status) {
            null, '', 'all' => $query,

            'in_stock' => $query->where('quantity', '>', 0),

            'low_stock' => $query
                ->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'notify_below'),

            'out_of_stock' => $query
                ->where('quantity', '=', 0)
                ->where('allow_backorder', '=', false),

            'backorder' => $query->where('allow_backorder', '=', true),

            default => $query, // unknown value: no-op (or throw validation error—see controller)
        };
    }
}
