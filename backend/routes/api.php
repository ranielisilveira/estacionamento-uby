<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\OperatorAuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OperatorController;
use App\Http\Controllers\Api\ParkingSpotController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json(['status' => 'ok', 'timestamp' => now()]);
    });

    // Operator authentication
    Route::post('operators/register', [OperatorAuthController::class, 'register']);
    Route::post('operators/login', [OperatorAuthController::class, 'login']);

    // Customer authentication
    Route::post('customers/register', [CustomerAuthController::class, 'register']);
    Route::post('customers/login', [CustomerAuthController::class, 'login']);

    // Email verification
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->name('verification.verify');
    Route::post('email/resend', [EmailVerificationController::class, 'resend'])
        ->name('verification.resend');

    // Address lookup via CEP
    Route::get('address/{zipCode}', [AddressController::class, 'getByZipCode']);

    // Public parking spots list (não requer autenticação)
    Route::get('parking-spots-available', [ParkingSpotController::class, 'available'])
        ->name('parking-spots.available');
});

// Protected routes - require authentication
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth user info and logout
    Route::post('operators/logout', [OperatorAuthController::class, 'logout']);
    Route::get('operators/me', [OperatorAuthController::class, 'me']);
    Route::get('operators/stats', [OperatorController::class, 'stats']);
    Route::post('customers/logout', [CustomerAuthController::class, 'logout']);
    Route::get('customers/me', [CustomerAuthController::class, 'me']);

    // Operators management
    Route::apiResource('operators', OperatorController::class);

    // Customers management
    Route::apiResource('customers', CustomerController::class);

    // Parking spots management
    Route::apiResource('parking-spots', ParkingSpotController::class);

    // Reservations management - rotas específicas ANTES do apiResource
    Route::get('reservations/search', [ReservationController::class, 'searchByPlate'])
        ->name('reservations.search-by-plate');
    Route::get('reservations/active-by-spot/{spotId}', [ReservationController::class, 'getActiveBySpot'])
        ->name('reservations.active-by-spot');
    Route::post('reservations/{id}/complete', [ReservationController::class, 'complete'])
        ->name('reservations.complete');
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');
    Route::post('reservations/{id}/operator-finalize', [ReservationController::class, 'operatorFinalize'])
        ->name('reservations.operator-finalize');
    Route::apiResource('reservations', ReservationController::class)->except(['update']);

    // Vehicles management
    Route::apiResource('vehicles', VehicleController::class);

    // Payments management
    Route::apiResource('payments', PaymentController::class)->except(['index']);
    Route::post('payments/{id}/mark-as-paid', [PaymentController::class, 'markAsPaid'])
        ->name('payments.mark-as-paid');
});
