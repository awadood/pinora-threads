<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\Customer\CustomerProfileResource;
use App\Services\Customer\CustomerProfileService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * CustomerProfileController
 *
 * Endpoints for viewing and updating the profile
 * of the currently authenticated customer.
 *
 * @author Abdul Wadood
 */
class CustomerProfileController extends Controller
{
    public function __construct(
        protected CustomerProfileService $service
    ) {
        $this->middleware('auth:sanctum');
    }

    public function show(Request $request): CustomerProfileResource
    {
        $profile = $this->service->getOrCreateForUser($request->user());

        return CustomerProfileResource::make($profile);
    }

    public function update(Request $request): CustomerProfileResource
    {
        $validated = $request->validate([
            'tax_class_id' => ['sometimes', 'integer', 'exists:tax_classes,id'],
            'marketing_email_opt_in' => ['sometimes', 'boolean'],
            'marketing_sms_opt_in' => ['sometimes', 'boolean'],
            'preferred_currency' => ['sometimes', 'string', 'size:3'],
        ]);

        $profile = $this->service->updateForUser($request->user(), $validated);

        return CustomerProfileResource::make($profile);
    }
}
