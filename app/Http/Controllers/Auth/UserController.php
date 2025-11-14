<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController
{
    public function __construct() {}

    /**
     * Get the authenticated user's profile data.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
