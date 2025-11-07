<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'loginCookie']); // SPA - cookie based

Route::middleware(['auth:web'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logoutCookie']);

    // User
    Route::get('/user', [UserController::class, 'user']);
});
