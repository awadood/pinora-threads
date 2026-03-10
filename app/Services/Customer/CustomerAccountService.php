<?php

namespace App\Services\Customer;

use App\Models\CustomerAccount;
use App\Models\Order;
use App\Repositories\Customer\Contracts\ICustomerAccountRepository;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * CustomerAccountService
 *
 * Orchestrates read/write operations for customer accounts,
 * including privacy-safe access for the currently authenticated user.
 *
 * @author Abdul Wadood
 */
class CustomerAccountService
{
    public function __construct(
        protected ICustomerAccountRepository $accounts
    ) {}

    public function getOrCreateForUser(Authenticatable $user, ?string $currencyHint = null): CustomerAccount
    {
        $existing = $this->findForUser($user);
        if ($existing) {
            $this->syncPreferredCurrency($existing, $user, $currencyHint);

            return $existing;
        }

        $currency = $this->resolvePreferredCurrency($user, $currencyHint);

        return $this->accounts->create([
            'user_id' => $user->getAuthIdentifier(),
            'marketing_email_opt_in' => false,
            'marketing_sms_opt_in' => false,
            'preferred_currency' => $currency,
        ]);
    }

    public function findForUser(Authenticatable $user): ?CustomerAccount
    {
        return $this->accounts->findByUserId($user->getAuthIdentifier());
    }

    public function updateForUser(Authenticatable $user, array $data, array $meta = []): CustomerAccount
    {
        $account = $this->getOrCreateForUser($user);
        unset($data['preferred_currency']);

        $source = $this->normalizeSource($meta['source'] ?? null);
        $ip = $this->normalizeIp($meta['ip'] ?? null);

        if (array_key_exists('marketing_email_opt_in', $data)) {
            $optIn = (bool) $data['marketing_email_opt_in'];
            if ($optIn) {
                $data['marketing_email_consented_at'] = now();
                $data['marketing_email_revoked_at'] = null;
            } else {
                $data['marketing_email_revoked_at'] = now();
            }
            $data['marketing_email_consent_source'] = $source;
            $data['marketing_email_consent_ip'] = $ip;
        }

        if (array_key_exists('marketing_sms_opt_in', $data)) {
            $optIn = (bool) $data['marketing_sms_opt_in'];
            if ($optIn) {
                $data['marketing_sms_consented_at'] = now();
                $data['marketing_sms_revoked_at'] = null;
            } else {
                $data['marketing_sms_revoked_at'] = now();
            }
            $data['marketing_sms_consent_source'] = $source;
            $data['marketing_sms_consent_ip'] = $ip;
        }

        $account->fill($data)->save();

        return $account->fresh();
    }

    private function syncPreferredCurrency(CustomerAccount $account, Authenticatable $user, ?string $currencyHint = null): void
    {
        $next = $this->resolvePreferredCurrency($user, $currencyHint);
        if ($account->preferred_currency === $next) {
            return;
        }

        $account->forceFill(['preferred_currency' => $next])->save();
    }

    private function resolvePreferredCurrency(Authenticatable $user, ?string $currencyHint = null): string
    {
        $hint = strtoupper((string) $currencyHint);
        if (strlen($hint) === 3) {
            return $hint;
        }

        $latestOrderCurrency = Order::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->latest('created_at')
            ->value('currency_code');

        $orderCurrency = strtoupper((string) $latestOrderCurrency);
        if (strlen($orderCurrency) === 3) {
            return $orderCurrency;
        }

        return strtoupper((string) config('storefront.default_currency', 'PKR'));
    }

    private function normalizeSource(mixed $source): string
    {
        $value = trim((string) $source);
        if ($value === '') {
            return 'account_page';
        }

        return substr($value, 0, 100);
    }

    private function normalizeIp(mixed $ip): ?string
    {
        $value = trim((string) $ip);
        if ($value === '') {
            return null;
        }

        return substr($value, 0, 45);
    }
}
