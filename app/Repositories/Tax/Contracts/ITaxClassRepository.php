<?php

namespace App\Repositories\Tax\Contracts;

use App\Repositories\IBaseRepository;

/**
 * ITaxClassRepository
 *
 * Repository contract for managing tax classes
 * (e.g., Retail US, Wholesale US, Shipping US).
 *
 * @author Abdul Wadood
 */
interface ITaxClassRepository extends IBaseRepository
{
    // For now, we only need the base CRUD and search methods.
}
