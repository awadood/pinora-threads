<?php

namespace App\Repositories\Customer;

use App\Models\CustomerAccount;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\ICustomerAccountRepository;

/**
 * CustomerAccountRepository
 *
 * Eloquent implementation for customer accounts.
 *
 * @author Abdul Wadood
 */
class CustomerAccountRepository extends BaseRepository implements ICustomerAccountRepository
{
    protected string $modelClass = CustomerAccount::class;

    public function findByUserId(int $userId): ?CustomerAccount
    {
        return $this->query()->where('user_id', $userId)->first();
    }
}
