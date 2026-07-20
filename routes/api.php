<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);

    Route::prefix('auth')->group(function () {
        require __DIR__.'/api/v1/auth.php';
    });

    Route::prefix('roadmap')->group(function () {
        require __DIR__.'/api/v1/roadmap.php';
    });

    Route::prefix('metrics')->group(function () {
        require __DIR__.'/api/v1/metrics.php';
    });
});
