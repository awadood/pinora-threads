<?php

namespace App\Repositories\Tax;

use App\Models\TaxClass;
use App\Repositories\BaseRepository;
use App\Repositories\Tax\Contracts\ITaxClassRepository;

/**
 * TaxClassRepository
 *
 * Eloquent-based repository for tax_classes table.
 *
 * @author Abdul Wadood
 */
class TaxClassRepository extends BaseRepository implements ITaxClassRepository
{
    protected string $modelClass = TaxClass::class;

    /**
     * Allow searching by tax class name if needed.
     *
     * @var array<string, true>
     */
    protected array $allowedSearchColumns = [
        'name' => true,
    ];
}
