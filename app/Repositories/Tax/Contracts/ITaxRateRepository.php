<?php

namespace App\Repositories\Tax\Contracts;

use App\Repositories\IBaseRepository;

/**
 * ITaxRateRepository
 *
 * Repository contract for managing tax rates which represent
 * geographical tax definitions (country/state/zip) and amounts.
 *
 * @author Abdul Wadood
 */
interface ITaxRateRepository extends IBaseRepository
{
    // Additional specialized lookups can be added later if needed.
}
