<?php

namespace App\Repositories\Tax\Contracts;

use App\Repositories\IBaseRepository;

/**
 * ITaxRuleRepository
 *
 * Repository contract for managing tax rules that define
 * priority, position and whether a rule applies to subtotal
 * and/or shipping for a given tax scenario.
 *
 * @author Abdul Wadood
 */
interface ITaxRuleRepository extends IBaseRepository
{
    // Base CRUD and search are sufficient at this stage.
}
