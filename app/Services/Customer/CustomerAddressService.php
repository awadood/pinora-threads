<?php

namespace App\Services\Customer;

use App\Models\CustomerAddress;
use App\Repositories\Customer\Contracts\ICustomerAddressRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * CustomerAddressService
 *
 * Encapsulates address CRUD operations and default flag handling
 * for a given user.
 *
 * @author Abdul Wadood
 */
class CustomerAddressService
{
    public function __construct(
        protected ICustomerAddressRepository $addresses,
        protected CustomerAccountService $accounts
    ) {}

    /**
     * @return \Illuminate\Support\Collection<int, CustomerAddress>
     */
    public function listForUser(Authenticatable $user)
    {
        return $this->addresses->forUser($user->getAuthIdentifier());
    }

    public function createForUser(Authenticatable $user, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $data): CustomerAddress {
            $data['user_id'] = $user->getAuthIdentifier();

            $address = $this->addresses->create($this->sanitizeAddressPayload($data));
            $this->applyDefaultSelections($user, $address, $data);

            return $address;
        });
    }

    public function updateForUser(Authenticatable $user, CustomerAddress $address, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($user, $address, $data): CustomerAddress {
            if ($address->user_id !== $user->getAuthIdentifier()) {
                abort(403);
            }

            $address->fill($this->sanitizeAddressPayload($data))->save();
            $this->applyDefaultSelections($user, $address, $data);

            return $address;
        });
    }

    public function deleteForUser(Authenticatable $user, CustomerAddress $address): void
    {
        if ($address->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        DB::transaction(function () use ($user, $address): void {
            $account = $this->accounts->findForUser($user);
            if ($account) {
                $updates = [];

                if ((int) $account->default_shipping_address_id === (int) $address->id) {
                    $updates['default_shipping_address_id'] = null;
                }

                if ((int) $account->default_billing_address_id === (int) $address->id) {
                    $updates['default_billing_address_id'] = null;
                }

                if ($updates !== []) {
                    $account->forceFill($updates)->save();
                }
            }

            $this->addresses->destroy($address->getKey());
        });
    }

    public function setDefaultShipping(Authenticatable $user, CustomerAddress $address): void
    {
        if ($address->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        $account = $this->accounts->getOrCreateForUser($user);
        $account->forceFill(['default_shipping_address_id' => $address->id])->save();
    }

    public function setDefaultBilling(Authenticatable $user, CustomerAddress $address): void
    {
        if ($address->user_id !== $user->getAuthIdentifier()) {
            abort(403);
        }

        $account = $this->accounts->getOrCreateForUser($user);
        $account->forceFill(['default_billing_address_id' => $address->id])->save();
    }

    /**
     * @return array{default_shipping_address_id: int|null, default_billing_address_id: int|null}
     */
    public function getDefaultAddressIdsForUser(Authenticatable $user): array
    {
        $account = $this->accounts->findForUser($user);

        return [
            'default_shipping_address_id' => $account?->default_shipping_address_id,
            'default_billing_address_id' => $account?->default_billing_address_id,
        ];
    }

    private function applyDefaultSelections(Authenticatable $user, CustomerAddress $address, array $data): void
    {
        if (! array_key_exists('default_shipping', $data) && ! array_key_exists('default_billing', $data)) {
            return;
        }

        $account = $this->accounts->getOrCreateForUser($user);
        $updates = [];

        if (array_key_exists('default_shipping', $data)) {
            $wantsDefault = (bool) $data['default_shipping'];
            $isCurrentDefault = (int) $account->default_shipping_address_id === (int) $address->id;
            $updates['default_shipping_address_id'] = $wantsDefault ? $address->id : ($isCurrentDefault ? null : $account->default_shipping_address_id);
        }

        if (array_key_exists('default_billing', $data)) {
            $wantsDefault = (bool) $data['default_billing'];
            $isCurrentDefault = (int) $account->default_billing_address_id === (int) $address->id;
            $updates['default_billing_address_id'] = $wantsDefault ? $address->id : ($isCurrentDefault ? null : $account->default_billing_address_id);
        }

        $account->forceFill($updates)->save();
    }

    private function sanitizeAddressPayload(array $data): array
    {
        unset($data['default_shipping'], $data['default_billing']);

        return $data;
    }
}
