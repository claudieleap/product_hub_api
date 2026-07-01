<?php

use App\Http\Controllers\Api\V1\RoadmapController;
use Illuminate\Support\Facades\Route;

$roadmapRoutes = function (string $type = 'saas') {
    Route::get('/state', [RoadmapController::class, 'state'])->defaults('type', $type);
    Route::post('/import', [RoadmapController::class, 'import'])->defaults('type', $type);
    Route::post('/sync', [RoadmapController::class, 'sync'])->defaults('type', $type);

    Route::post('/items', [RoadmapController::class, 'storeItem'])->defaults('type', $type);
    Route::put('/items/{id}', [RoadmapController::class, 'updateItem'])->defaults('type', $type);
    Route::patch('/items/{id}', [RoadmapController::class, 'updateItem'])->defaults('type', $type);
    Route::delete('/items/{id}', [RoadmapController::class, 'destroyItem'])->defaults('type', $type);
    Route::delete('/products/{productId}/items', [RoadmapController::class, 'destroyItemsByProduct'])->defaults('type', $type);

    Route::post('/products', [RoadmapController::class, 'storeProduct'])->defaults('type', $type);
    Route::put('/products/{id}', [RoadmapController::class, 'updateProduct'])->defaults('type', $type);
    Route::patch('/products/{id}', [RoadmapController::class, 'updateProduct'])->defaults('type', $type);
    Route::delete('/products/{id}', [RoadmapController::class, 'destroyProduct'])->defaults('type', $type);
};

Route::prefix('{type}')
    ->where(['type' => 'saas|interno|bpo'])
    ->group(fn () => $roadmapRoutes());

$roadmapRoutes('saas');
