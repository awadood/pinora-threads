<?php

namespace App\Repositories\Inventory;

use App\Models\Stock;
use App\Repositories\BaseRepository;
use App\Repositories\Inventory\Contracts\IStockRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * StockRepository
 *
 * Eloquent implementation for stock locations.
 *
 * @author Abdul Wadood
 */
class StockRepository extends BaseRepository implements IStockRepository
{
    protected string $modelClass = Stock::class;

    protected array $allowedSearchColumns = [
        'title' => true,
    ];

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()
            ->withCount([
                'stockLevels as total_variants' => function ($query) {
                    $query->select(DB::raw('COUNT(DISTINCT variant_id)'));
                },
            ])
            ->withSum('stockLevels as total_quantity', 'quantity')
            ->get();
    }
}
