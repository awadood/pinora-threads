<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // SPA cookie session
    public function loginCookie(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (! auth()->attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        $request->session()->regenerate();

        return response()->json(['user' => $request->user()]);
    }

    // PAT (mobile/integrations)
    public function loginToken(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (! auth()->attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        $user = $request->user();
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json(['user' => $request->user(), 'token' => $token]);
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
}
