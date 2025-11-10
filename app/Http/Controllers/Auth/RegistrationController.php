<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Rules\PhoneNumberRule;
use App\Models\CustomerGroup;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Util\Constant;
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

        //$user->assignRole('customer'); // Optional

        CustomerProfile::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'tax_class_id' => 1,      // safe default
            'preferred_currency' => 'PKR',  // or infer from locale
            'marketing_email_opt_in' => false,
            'marketing_sms_opt_in' => false,
        ]);
        //event(new Registered($user)); // Fire Registered event (hooks for emails/analytics)
        //$user->sendEmailVerificationNotification(); //Optional - how to handle notifications

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
