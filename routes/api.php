<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\SortingSessionController;
use App\Http\Controllers\API\SortingLogController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Tomato Sorter API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard (role-aware)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Admin only: manage users
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Appointments
    Route::apiResource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

    // Sorting sessions (sorter + admin)
    Route::middleware('role:sorter,admin')->group(function () {
        Route::apiResource('appointments.sessions', SortingSessionController::class)
            ->shallow();
        Route::post('/sessions/{session}/complete', [SortingSessionController::class, 'complete']);
    });

    // Sorting logs (created by Raspberry Pi / sorter)
    Route::middleware('role:sorter,admin')->group(function () {
        Route::apiResource('sessions.logs', SortingLogController::class)
            ->shallow()->only(['index', 'store', 'show']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show']);
    // Available sorters (accessible by farmers)
    Route::get('/sorters', [UserController::class, 'sorters']);
});
