<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email:rfc,dns']);

        // Always return 200 to avoid user enumeration
        Password::sendResetLink(['email' => $request->email]);

        return response()->json(['message' => __('auth.sent')]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'nullable', 'string'],
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['status' => __($status)], 200);
    }
}
