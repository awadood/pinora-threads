<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'loginCookie']); // SPA - cookie based

Route::middleware(['auth:web'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logoutCookie']);
});
