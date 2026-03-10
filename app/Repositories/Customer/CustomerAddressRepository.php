<?php

namespace App\Repositories\Customer;

use App\Models\CustomerAddress;
use App\Repositories\BaseRepository;
use App\Repositories\Customer\Contracts\ICustomerAddressRepository;
use Illuminate\Support\Collection;

/**
 * CustomerAddressRepository
 *
 * Eloquent implementation for customer addresses.
 *
 * @author Abdul Wadood
 */
class CustomerAddressRepository extends BaseRepository implements ICustomerAddressRepository
{
    protected string $modelClass = CustomerAddress::class;

    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }
}
