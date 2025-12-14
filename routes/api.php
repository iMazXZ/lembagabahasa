<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OfficeController;
use App\Http\Controllers\Api\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Attendance API untuk aplikasi mobile
|
*/

// Public routes (tidak perlu auth)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (perlu auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Offices
    Route::get('/offices', [OfficeController::class, 'index']);

    // Attendance
    Route::prefix('attendance')->group(function () {
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
        Route::post('/{id}/update-notes', [AttendanceController::class, 'updateNotes']);
        Route::get('/history', [AttendanceController::class, 'history']);
    });
});
