<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);

    //User
    Route::get('/user', [UserController::class, 'user']);
});