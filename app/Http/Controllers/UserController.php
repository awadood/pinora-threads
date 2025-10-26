<?php

namespace App\Http\Controllers;

use App\Repository\Contracts\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private IUserRepository $repository)
    { }

    /**
     * Get the authenticated user's profile data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
