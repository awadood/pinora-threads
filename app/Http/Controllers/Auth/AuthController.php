<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Rules\PhoneNumberRule;
use App\Services\Order\OrderClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // SPA cookie session
    public function loginCookie(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required', 'remember' => 'sometimes|boolean']);

        if (! auth()->attempt($request->only('email', 'password'), $request->input('remember', false))) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = $request->user();
        app(OrderClaimService::class)->claimOrdersForUser($user);

        return response()->json();
    }

    // PAT (mobile/integrations)
    public function loginToken(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required', 'remember' => 'sometimes|boolean']);

        if (! auth()->attempt($request->only('email', 'password'), $request->input('remember', false))) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        $user = $request->user();
        app(OrderClaimService::class)->claimOrdersForUser($user);
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logoutCookie(Request $request)
    {
        auth()->guard()->logout(); // ends cookie session if present
        $request->session()->invalidate();
        $request->session()->regenerateToken(); // rotate CSRF to avoid 419s on next request

        return response()->json([], 204);
    }

    public function logoutToken(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([], 204);
    }

    public function user(Request $request)
    {
        return UserResource::make($request->user());
    }

    public function updateUser(Request $request): UserResource
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', new PhoneNumberRule, 'unique:users,phone,'.$user->id],
        ]);

        if ($user->email !== $data['email']) {
            $user->email_verified_at = null;
        }

        if ($user->phone !== $data['phone']) {
            $user->phone_verified_at = null;
        }

        $user->fill($data)->save();

        return UserResource::make($user->fresh());
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
