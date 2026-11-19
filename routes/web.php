<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AuditLogWebController;
use App\Http\Controllers\Web\CarrierWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExceptionWebController;
use App\Http\Controllers\Web\LocationWebController;
use App\Http\Controllers\Web\NotificationWebController;
use App\Http\Controllers\Web\ShipmentWebController;
use App\Http\Controllers\Web\TrackingWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CargoTrace Platform
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments (List & Show accessible to Customer, Operator, Admin)
    Route::get('/shipments', [ShipmentWebController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{shipment}', [ShipmentWebController::class, 'show'])->name('shipments.show');

    // Notifications
    Route::get('/notifications', [NotificationWebController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationWebController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationWebController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Logistics Operator & Administrator Actions
    Route::middleware('role:ADMIN,OPERATOR')->group(function () {
        Route::get('/shipment/create', [ShipmentWebController::class, 'create'])->name('shipments.create');
        Route::post('/shipments', [ShipmentWebController::class, 'store'])->name('shipments.store');
        Route::get('/shipments/{shipment}/edit', [ShipmentWebController::class, 'edit'])->name('shipments.edit');
        Route::put('/shipments/{shipment}', [ShipmentWebController::class, 'update'])->name('shipments.update');
        Route::post('/shipments/{shipment}/status', [ShipmentWebController::class, 'updateStatus'])->name('shipments.status');

        // Tracking Events & Exception Logging
        Route::post('/shipments/{shipment}/tracking', [TrackingWebController::class, 'store'])->name('shipments.tracking.store');
        Route::get('/exceptions', [ExceptionWebController::class, 'index'])->name('exceptions.index');
        Route::post('/shipments/{shipment}/exceptions', [ExceptionWebController::class, 'store'])->name('shipments.exceptions.store');
        Route::patch('/exceptions/{exception}/status', [ExceptionWebController::class, 'updateStatus'])->name('exceptions.status');

        // Geocoding helper API
        Route::get('/locations/geocode', [LocationWebController::class, 'geocode'])->name('locations.geocode');
    });

    // Administrator Only Configurations
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/carriers', [CarrierWebController::class, 'index'])->name('carriers.index');
        Route::post('/carriers', [CarrierWebController::class, 'store'])->name('carriers.store');

        Route::get('/locations', [LocationWebController::class, 'index'])->name('locations.index');
        Route::post('/locations', [LocationWebController::class, 'store'])->name('locations.store');

        Route::get('/audit-logs', [AuditLogWebController::class, 'index'])->name('audit-logs.index');
    });
});
