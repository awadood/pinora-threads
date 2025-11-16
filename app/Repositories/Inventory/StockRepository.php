<?php

namespace App\Repositories\Inventory;

use App\Models\Stock;
use App\Repositories\BaseRepository;
use App\Repositories\Inventory\Contracts\IStockRepository;

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
}
