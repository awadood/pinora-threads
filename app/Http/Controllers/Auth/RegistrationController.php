<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Rules\PhoneNumberRule;
use App\Models\CustomerStat;
use App\Models\CustomerGroup;
use App\Models\CustomerAccount;
use App\Models\User;
use App\Support\Constant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', new PhoneNumberRule],
            'password' => ['required', Password::defaults()],
            'issue_token' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        $standardGroup = CustomerGroup::where('code', Constant::STANDARD)->first();
        $user->customerGroups()->syncWithoutDetaching([$standardGroup->id]);

        // TODO consider setting the customer for role, profile, tax, events, notificaitons

        // $user->assignRole('customer'); // Optional

        CustomerAccount::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'preferred_currency' => strtoupper((string) config('storefront.default_currency', 'PKR')),
            'marketing_email_opt_in' => false,
            'marketing_sms_opt_in' => false,
        ]);

        CustomerStat::firstOrCreate([
            'user_id' => $user->id,
        ]);
        // event(new Registered($user)); // Fire Registered event (hooks for emails/analytics)
        // $user->sendEmailVerificationNotification(); //Optional - how to handle notifications

        $response = ['user' => $user];

        // check mode cookie or token
        if ($request->boolean('issue_token')) {
            $response['token'] = $user->createToken('access_token')->plainTextToken;
        } else {
            auth()->login($user);
        }

        return response()->json($response, 201);
    }
}
