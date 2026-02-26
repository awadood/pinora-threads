<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| web guarded endpoints
|--------------------------------------------------------------------------
|
| As the application will be deployed on the same domain like https://pinorathreads.com/admin/api,
| therefore, all the routes will be prefixed with /api
|
*/

Route::post('/api/auth/login', [AuthController::class, 'loginCookie']); // SPA - cookie based

Route::middleware(['auth:web'])->group(function () {
    // Auth
    Route::post('/api/auth/logout', [AuthController::class, 'logoutCookie']);
});

// Email preview (local/dev only)
Route::get('/preview/emails/order-placed', function (App\Services\Order\OrderClaimService $claimService) {
    abort_unless(app()->environment(['local', 'development']), 404);

    $order = App\Models\Order::with('items')->findOrFail(4);

    return new App\Mail\OrderPlacedMail(
        $order,
        $claimService->buildTrackingUrl($order),
        $claimService->buildClaimUrl($order),
    );
});
