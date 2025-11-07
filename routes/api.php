<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'loginToken']); // PAT

Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logoutToken']);

    // User
    Route::get('/user', [UserController::class, 'user']);

    // add middleware permission:permission-name where needed

    // Catalog
    Route::get('/products/a', [UserController::class, 'a']);
});
