<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth.token')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Somente admin gerencia contas.
    Route::middleware('admin')->group(function () {
        Route::get('/users', [AuthController::class, 'listUsers']);
        Route::post('/users', [AuthController::class, 'createUser']);
        Route::post('/users/{id}/reset-password', [AuthController::class, 'resetPassword']);
        Route::put('/users/{id}/role', [AuthController::class, 'updateRole']);
        Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
    });
});
