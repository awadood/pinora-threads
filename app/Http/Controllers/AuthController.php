<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate the input
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        // 2. Attempt authentication
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // 3. Get the authenticated user
        $user = $request->user();

        // 4. Create and return the API token. Token name is arbitrary, but required by Sanctum
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Destroy an authenticated session. (Logout)
     */
    public function logout(Request $request)
    {
        // Delete the current access token being used
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }
}
