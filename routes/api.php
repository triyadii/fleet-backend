<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\TelemetryController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{uuid}', [UserController::class, 'show']);
    Route::put('/users/{uuid}', [UserController::class, 'update']);
    Route::delete('/users/{uuid}', [UserController::class, 'destroy']);

    Route::post('/telemetry', [TelemetryController::class, 'store']);
    Route::get('/telemetry/latest', [TelemetryController::class, 'latest']);
    Route::get('/telemetry/{vehicle_id}', [TelemetryController::class, 'history']);

    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::get('/vehicles/{uuid}', [VehicleController::class, 'show']);
    Route::put('/vehicles/{uuid}', [VehicleController::class, 'update']);
    Route::delete('/vehicles/{uuid}', [VehicleController::class, 'destroy']);
});
