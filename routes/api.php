<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\CarrierApiController;
use App\Http\Controllers\Api\ExceptionApiController;
use App\Http\Controllers\Api\LocationApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use App\Http\Controllers\Api\TrackingApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - CargoTrace RESTful Services
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

// Protected API Routes (Sanctum Authenticated)
Route::middleware('auth:sanctum')->group(function () {
    // Current Authenticated User & Session Revocation
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);

    // Shipments (List & Show scoped by RBAC: Customers see own, Operators/Admins see all)
    Route::get('/shipments', [ShipmentApiController::class, 'index']);
    Route::get('/shipments/{id}', [ShipmentApiController::class, 'show']);

    // Tracking timeline
    Route::get('/shipments/{id}/tracking', [TrackingApiController::class, 'index']);

    // Exceptions list for a shipment
    Route::get('/shipments/{id}/exceptions', [ExceptionApiController::class, 'index']);

    // Carriers & Locations Read
    Route::get('/carriers', [CarrierApiController::class, 'index']);
    Route::get('/locations', [LocationApiController::class, 'index']);

    // Logistics Operator & Administrator Actions
    Route::middleware('role:ADMIN,OPERATOR')->group(function () {
        Route::post('/shipments', [ShipmentApiController::class, 'store']);
        Route::match(['put', 'patch'], '/shipments/{id}', [ShipmentApiController::class, 'update']);
        Route::post('/shipments/{id}/status', [ShipmentApiController::class, 'updateStatus']);

        Route::post('/shipments/{id}/tracking', [TrackingApiController::class, 'store']);
        Route::post('/shipments/{id}/exceptions', [ExceptionApiController::class, 'store']);
        Route::patch('/exceptions/{id}', [ExceptionApiController::class, 'updateStatus']);
    });

    // Administrator Only Actions
    Route::middleware('role:ADMIN')->group(function () {
        Route::delete('/shipments/{id}', [ShipmentApiController::class, 'destroy']);
        Route::post('/carriers', [CarrierApiController::class, 'store']);
        Route::post('/locations', [LocationApiController::class, 'store']);
    });
});
