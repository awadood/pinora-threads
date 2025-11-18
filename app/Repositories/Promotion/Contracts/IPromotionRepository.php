<?php

namespace App\Repositories\Promotion\Contracts;

use App\Models\Promotion;
use App\Repositories\IBaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * IPromotionRepository
 *
 * Contract for working with Promotions, including basic CRUD and
 * domain-specific queries such as listing active promotions for
 * the storefront or retrieving admin-filtered lists.
 *
 * @author Abdul Wadood
 */
interface IPromotionRepository extends IBaseRepository
{
    /**
     * Return currently active promotions for storefront consumption.
     *
     * A promotion is considered public-active if:
     *  - active = true
     *  - status = 'ongoing'
     *  - from_date <= $now
     *  - (to_date is null OR to_date >= $now)
     *
     * @return Collection<int, Promotion>
     */
    public function getPublicActive(Carbon $now): Collection;
}
