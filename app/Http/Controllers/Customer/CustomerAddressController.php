<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\CustomerAddressResource;
use App\Models\CustomerAddress;
use App\Services\Customer\CustomerAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * CustomerAddressController
 *
 * Endpoints for managing customer saved addresses.
 *
 * @author Abdul Wadood
 */
class CustomerAddressController extends Controller
{
    public function __construct(protected CustomerAddressService $service) {}

    public function index(Request $request)
    {
        $this->attachDefaultAddressContext($request);
        $items = $this->service->listForUser($request->user());

        return CustomerAddressResource::collection($items);
    }

    public function store(Request $request): CustomerAddressResource
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state_code' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2', 'exists:countries,code'],
            'phone' => ['nullable', 'string', 'max:14'],
            'default_shipping' => ['sometimes', 'boolean'],
            'default_billing' => ['sometimes', 'boolean'],
        ]);

        $address = $this->service->createForUser($request->user(), $validated);
        $this->attachDefaultAddressContext($request);

        return CustomerAddressResource::make($address);
    }

    public function update(Request $request, CustomerAddress $address): CustomerAddressResource
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state_code' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2', 'exists:countries,code'],
            'phone' => ['nullable', 'string', 'max:14'],
            'default_shipping' => ['sometimes', 'boolean'],
            'default_billing' => ['sometimes', 'boolean'],
        ]);

        $address = $this->service->updateForUser($request->user(), $address, $validated);
        $this->attachDefaultAddressContext($request);

        return CustomerAddressResource::make($address);
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->service->deleteForUser($request->user(), $address);

        return response()->json([], 204);
    }

    public function setDefaultShipping(Request $request, CustomerAddress $address): CustomerAddressResource
    {
        $this->service->setDefaultShipping($request->user(), $address);
        $this->attachDefaultAddressContext($request);

        return CustomerAddressResource::make($address->fresh());
    }

    public function setDefaultBilling(Request $request, CustomerAddress $address): CustomerAddressResource
    {
        $this->service->setDefaultBilling($request->user(), $address);
        $this->attachDefaultAddressContext($request);

        return CustomerAddressResource::make($address->fresh());
    }

    private function attachDefaultAddressContext(Request $request): void
    {
        $defaults = $this->service->getDefaultAddressIdsForUser($request->user());
        $request->attributes->set('default_shipping_address_id', $defaults['default_shipping_address_id']);
        $request->attributes->set('default_billing_address_id', $defaults['default_billing_address_id']);
    }
}
