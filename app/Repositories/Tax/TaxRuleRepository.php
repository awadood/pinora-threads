<?php

namespace App\Repositories\Tax;

use App\Models\TaxRule;
use App\Repositories\BaseRepository;
use App\Repositories\Tax\Contracts\ITaxRuleRepository;

/**
 * TaxRuleRepository
 *
 * Eloquent-based repository for tax_rules table.
 *
 * @author Abdul Wadood
 */
class TaxRuleRepository extends BaseRepository implements ITaxRuleRepository
{
    protected string $modelClass = TaxRule::class;

    /**
     * You can whitelist columns useful for search/filter if needed.
     *
     * @var array<string, true>
     */
    protected array $allowedSearchColumns = [
        'code' => true,
        'active' => true,
    ];
}
