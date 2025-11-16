<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\AddressResource;
use App\Models\Address;
use App\Services\Customer\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * AddressController
 *
 * Endpoints for managing customer saved addresses.
 *
 * @author Abdul Wadood
 */
class AddressController extends Controller
{
    public function __construct(
        protected AddressService $service
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $items = $this->service->listForUser($request->user());

        return AddressResource::collection($items);
    }

    public function store(Request $request): AddressResource
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
            'phone' => ['nullable', 'string', 'max:255'],
            'default_shipping' => ['sometimes', 'boolean'],
            'default_billing' => ['sometimes', 'boolean'],
        ]);

        $address = $this->service->createForUser($request->user(), $validated);

        return new AddressResource($address);
    }

    public function update(Request $request, Address $address): AddressResource
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
            'phone' => ['nullable', 'string', 'max:255'],
            'default_shipping' => ['sometimes', 'boolean'],
            'default_billing' => ['sometimes', 'boolean'],
        ]);

        $address = $this->service->updateForUser($request->user(), $address, $validated);

        return new AddressResource($address);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->service->deleteForUser($request->user(), $address);

        return response()->json([], 204);
    }

    public function setDefaultShipping(Request $request, Address $address): AddressResource
    {
        $this->service->setDefaultShipping($request->user(), $address);

        return new AddressResource($address->fresh());
    }

    public function setDefaultBilling(Request $request, Address $address): AddressResource
    {
        $this->service->setDefaultBilling($request->user(), $address);

        return new AddressResource($address->fresh());
    }
}
