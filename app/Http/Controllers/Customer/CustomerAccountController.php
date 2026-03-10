<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\CustomerAccountResource;
use App\Services\Customer\CustomerAccountService;
use App\Support\Storefront\StoreContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * CustomerAccountController
 *
 * Endpoints for viewing and updating the account
 * of the currently authenticated customer.
 *
 * @author Abdul Wadood
 */
class CustomerAccountController extends Controller
{
    public function __construct(protected CustomerAccountService $service) {}

    public function show(Request $request): CustomerAccountResource
    {
        $ctx = $request->attributes->get('store_ctx') ?? app(StoreContext::class);
        $account = $this->service->getOrCreateForUser($request->user(), $ctx?->currency);

        return CustomerAccountResource::make($account);
    }

    public function upsert(Request $request): CustomerAccountResource
    {
        $validated = $request->validate([
            'marketing_email_opt_in' => ['sometimes', 'boolean'],
            'marketing_sms_opt_in' => ['sometimes', 'boolean'],
            'consent_source' => ['sometimes', 'string', 'max:100'],
        ]);

        $account = $this->service->updateForUser($request->user(), $validated, [
            'ip' => $request->ip(),
            'source' => $validated['consent_source'] ?? 'account_page',
        ]);

        return CustomerAccountResource::make($account);
    }

    public function update(Request $request): CustomerAccountResource
    {
        return $this->upsert($request);
    }
}
