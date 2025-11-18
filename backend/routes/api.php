<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OperatorController;
use App\Http\Controllers\Api\ParkingSpotController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes will be added here
});

// Protected routes - require authentication
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Operators management
    Route::apiResource('operators', OperatorController::class);
    
    // Customers management
    Route::apiResource('customers', CustomerController::class);
    
    // Parking spots management
    Route::apiResource('parking-spots', ParkingSpotController::class);
    Route::get('parking-spots-available', [ParkingSpotController::class, 'available'])
        ->name('parking-spots.available');
    
    // Reservations management
    Route::apiResource('reservations', ReservationController::class)->except(['update']);
    Route::post('reservations/{id}/complete', [ReservationController::class, 'complete'])
        ->name('reservations.complete');
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');
});
