<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public routes (for testing without auth)
Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/{student}', [StudentController::class, 'show']);
Route::get('/attendances', [AttendanceController::class, 'index']);
Route::get('/attendances/today-summary', [AttendanceController::class, 'todaysSummary']);
Route::get('/attendances/stats-by-date', [AttendanceController::class, 'statsByDate']);
Route::get('/attendances/monthly-report', [AttendanceController::class, 'monthlyReport']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Student management
    Route::post('/students', [StudentController::class, 'store']);
    Route::put('/students/{student}', [StudentController::class, 'update']);
    Route::delete('/students/{student}', [StudentController::class, 'destroy']);

    // Attendance management
    Route::post('/attendances', [AttendanceController::class, 'store']);
    Route::post('/attendances/bulk', [AttendanceController::class, 'bulkStore']);
    Route::get('/attendances/{attendance}', [AttendanceController::class, 'show']);
    Route::put('/attendances/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy']);
});