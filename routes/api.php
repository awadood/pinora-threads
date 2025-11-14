<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Core\CountryController;
use App\Http\Controllers\Core\CurrencyController;
use App\Http\Controllers\Core\CustomerGroupController;
use App\Http\Controllers\Core\InvoiceStatusController;
use App\Http\Controllers\Core\OrderStatusController;
use App\Http\Controllers\Core\PaymentMethodController;
use App\Http\Controllers\Core\PaymentStatusController;
use App\Http\Controllers\Core\RefundStatusController;
use App\Http\Controllers\Core\ShipmentMethodController;
use App\Http\Controllers\Core\ShipmentStatusController;
use App\Http\Controllers\Core\StateController;
use App\Http\Controllers\Core\StockMovementTypeController;
use App\Support\Permissions as P;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public endpoints
|--------------------------------------------------------------------------
|
| The following routes are mostly customer facing storefront or readonly.
|
*/

// login and Password flows
Route::post('/login', [AuthController::class, 'loginToken']); // PAT
Route::post('/register', [RegistrationController::class, 'register'])->middleware('throttle:10,1');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

// Core tables read only operations
Route::get('countries', [CountryController::class, 'index']);
Route::get('countries/{country}', [CountryController::class, 'show']);
Route::get('states', [StateController::class, 'index']);
Route::get('states/{state}', [StateController::class, 'show']);
Route::get('currencies', [CurrencyController::class, 'index']);
Route::get('currencies/{currency}', [CurrencyController::class, 'show']);
Route::get('customer-groups', [CustomerGroupController::class, 'index']);
Route::get('customer-groups/{customer_group}', [CustomerGroupController::class, 'show']);
Route::get('order-statuses', [OrderStatusController::class, 'index']);
Route::get('order-statuses/{order_status}', [OrderStatusController::class, 'show']);
Route::get('shipment-statuses', [ShipmentStatusController::class, 'index']);
Route::get('shipment-statuses/{shipment_status}', [ShipmentStatusController::class, 'show']);
Route::get('payment-statuses', [PaymentStatusController::class, 'index']);
Route::get('payment-statuses/{payment_status}', [PaymentStatusController::class, 'show']);
Route::get('invoice-statuses', [InvoiceStatusController::class, 'index']);
Route::get('invoice-statuses/{invoice_status}', [InvoiceStatusController::class, 'show']);
Route::get('refund-statuses', [RefundStatusController::class, 'index']);
Route::get('refund-statuses/{refund_status}', [RefundStatusController::class, 'show']);
Route::get('payment-methods', [PaymentMethodController::class, 'index']);
Route::get('payment-methods/{payment_method}', [PaymentMethodController::class, 'show']);
Route::get('shipment-methods', [ShipmentMethodController::class, 'index']);
Route::get('shipment-methods/{shipment_method}', [ShipmentMethodController::class, 'show']);
Route::get('stock-movement-types', [StockMovementTypeController::class, 'index']);
Route::get('stock-movement-types/{stock_movement_type}', [StockMovementTypeController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected core table routes — create, update, destroy
|--------------------------------------------------------------------------
|
| These routes are barley used for managing status tables
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Countries
    Route::post('countries', [CountryController::class, 'store'])->middleware('permission:'.P::CTRY_CREATE);
    Route::put('countries/{country}', [CountryController::class, 'update'])->middleware('permission:'.P::CTRY_UPDATE);
    Route::delete('countries/{country}', [CountryController::class, 'destroy'])->middleware('permission:'.P::CTRY_DESTROY);

    // States
    Route::post('states', [StateController::class, 'store'])->middleware('permission:'.P::STATE_CREATE);
    Route::put('states/{state}', [StateController::class, 'update'])->middleware('permission:'.P::STATE_UPDATE);
    Route::delete('states/{state}', [StateController::class, 'destroy'])->middleware('permission:'.P::STATE_DESTROY);

    // Currencies
    Route::post('currencies', [CurrencyController::class, 'store'])->middleware('permission:'.P::CURR_CREATE);
    Route::put('currencies/{currency}', [CurrencyController::class, 'update'])->middleware('permission:'.P::CURR_UPDATE);
    Route::delete('currencies/{currency}', [CurrencyController::class, 'destroy'])->middleware('permission:'.P::CURR_DESTROY);

    // Customer Groups
    Route::post('customer-groups', [CustomerGroupController::class, 'store'])->middleware('permission:'.P::CGRP_CREATE);
    Route::put('customer-groups/{customer_group}', [CustomerGroupController::class, 'update'])->middleware('permission:'.P::CGRP_UPDATE);
    Route::delete('customer-groups/{customer_group}', [CustomerGroupController::class, 'destroy'])->middleware('permission:'.P::CGRP_DESTROY);

    // Order Statuses
    Route::post('order-statuses', [OrderStatusController::class, 'store'])->middleware('permission:'.P::ORST_CREATE);
    Route::put('order-statuses/{order_status}', [OrderStatusController::class, 'update'])->middleware('permission:'.P::ORST_UPDATE);
    Route::delete('order-statuses/{order_status}', [OrderStatusController::class, 'destroy'])->middleware('permission:'.P::ORST_DESTROY);

    // Shipment Statuses
    Route::post('shipment-statuses', [ShipmentStatusController::class, 'store'])->middleware('permission:'.P::SHST_CREATE);
    Route::put('shipment-statuses/{shipment_status}', [ShipmentStatusController::class, 'update'])->middleware('permission:'.P::SHST_UPDATE);
    Route::delete('shipment-statuses/{shipment_status}', [ShipmentStatusController::class, 'destroy'])->middleware('permission:'.P::SHST_DESTROY);

    // Payment Statuses
    Route::post('payment-statuses', [PaymentStatusController::class, 'store'])->middleware('permission:'.P::PYST_CREATE);
    Route::put('payment-statuses/{payment_status}', [PaymentStatusController::class, 'update'])->middleware('permission:'.P::PYST_UPDATE);
    Route::delete('payment-statuses/{payment_status}', [PaymentStatusController::class, 'destroy'])->middleware('permission:'.P::PYST_DESTROY);

    // Invoice Statuses
    Route::post('invoice-statuses', [InvoiceStatusController::class, 'store'])->middleware('permission:'.P::IVST_CREATE);
    Route::put('invoice-statuses/{invoice_status}', [InvoiceStatusController::class, 'update'])->middleware('permission:'.P::IVST_UPDATE);
    Route::delete('invoice-statuses/{invoice_status}', [InvoiceStatusController::class, 'destroy'])->middleware('permission:'.P::IVST_DESTROY);

    // Refund Statuses
    Route::post('refund-statuses', [RefundStatusController::class, 'store'])->middleware('permission:'.P::RFST_CREATE);
    Route::put('refund-statuses/{refund_status}', [RefundStatusController::class, 'update'])->middleware('permission:'.P::RFST_UPDATE);
    Route::delete('refund-statuses/{refund_status}', [RefundStatusController::class, 'destroy'])->middleware('permission:'.P::RFST_DESTROY);

    // Payment Methods
    Route::post('payment-methods', [PaymentMethodController::class, 'store'])->middleware('permission:'.P::PYMT_CREATE);
    Route::put('payment-methods/{payment_method}', [PaymentMethodController::class, 'update'])->middleware('permission:'.P::PYMT_UPDATE);
    Route::delete('payment-methods/{payment_method}', [PaymentMethodController::class, 'destroy'])->middleware('permission:'.P::PYMT_DESTROY);

    // Shipment Methods
    Route::post('shipment-methods', [ShipmentMethodController::class, 'store'])->middleware('permission:'.P::SHMT_CREATE);
    Route::put('shipment-methods/{shipment_method}', [ShipmentMethodController::class, 'update'])->middleware('permission:'.P::SHMT_UPDATE);
    Route::delete('shipment-methods/{shipment_method}', [ShipmentMethodController::class, 'destroy'])->middleware('permission:'.P::SHMT_DESTROY);

    // Stock Movement Types
    Route::post('stock-movement-types', [StockMovementTypeController::class, 'store'])->middleware('permission:'.P::SMT_CREATE);
    Route::put('stock-movement-types/{stock_movement_type}', [StockMovementTypeController::class, 'update'])->middleware('permission:'.P::SMT_UPDATE);
    Route::delete('stock-movement-types/{stock_movement_type}', [StockMovementTypeController::class, 'destroy'])->middleware('permission:'.P::SMT_DESTROY);
});

/*
|--------------------------------------------------------------------------
| Protected mutations
|--------------------------------------------------------------------------
|
| The following routes are mostly role based access.
|
*/

// sanctum protected APIs
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logoutToken']);
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1']);

    // User
    Route::get('/user', [UserController::class, 'user']);

    // Catalog

    // Content

    // Core

    // Customer

    // Inventory

    // Order

    // Payment

    // Promotion

    // Shipping

    // Tax

});
