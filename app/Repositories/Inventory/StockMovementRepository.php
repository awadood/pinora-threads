<?php

namespace App\Repositories\Inventory;

use App\Models\StockMovement;
use App\Repositories\BaseRepository;
use App\Repositories\Inventory\Contracts\IStockMovementRepository;

/**
 * StockMovementRepository
 *
 * Eloquent implementation for stock movement log.
 *
 * @author Abdul Wadood
 */
class StockMovementRepository extends BaseRepository implements IStockMovementRepository
{
    protected string $modelClass = StockMovement::class;

    protected array $allowedSearchColumns = [
        'stock_id' => true,
        'product_id' => true,
        'stock_movement_type_code' => true,
    ];
}
