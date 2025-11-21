<?php

namespace App\Repositories\Tax;

use App\Models\TaxRate;
use App\Repositories\BaseRepository;
use App\Repositories\Tax\Contracts\ITaxRateRepository;

/**
 * TaxRateRepository
 *
 * Eloquent-based repository for tax_rates table.
 *
 * @author Abdul Wadood
 */
class TaxRateRepository extends BaseRepository implements ITaxRateRepository
{
    protected string $modelClass = TaxRate::class;

    /**
     * Whitelisted columns for search().
     *
     * @var array<string, true>
     */
    protected array $allowedSearchColumns = [
        'code' => true,
        'country_code' => true,
        'state_code' => true,
        'zipcode' => true,
        'active' => true,
    ];
}
