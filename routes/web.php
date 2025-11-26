<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| web guarded endpoints
|--------------------------------------------------------------------------
|
| As the application will be deployed on the same domain like https://pinnorafashion.com/api,
| therefore, all the routes will be prefixed with /api
|
*/

Route::post('/api/auth/login', [AuthController::class, 'loginCookie']); // SPA - cookie based

Route::middleware(['auth:web'])->group(function () {
    // Auth
    Route::post('/api/auth/logout', [AuthController::class, 'logoutCookie']);
});
