<?php

namespace App\Repositories\Customer\Contracts;

use App\Repositories\IBaseRepository;

/**
 * ICustomerAccountRepository
 *
 * Repository contract for managing customer account records.
 * Provides access helpers for the currently authenticated user
 * and admin-level lookup by user id.
 *
 * @author Abdul Wadood
 */
interface ICustomerAccountRepository extends IBaseRepository
{
    /**
     * Find an account by the owning user id.
     */
    public function findByUserId(int $userId): ?\App\Models\CustomerAccount;
}
