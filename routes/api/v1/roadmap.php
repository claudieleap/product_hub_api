<?php

use App\Http\Controllers\Api\V1\RoadmapController;
use Illuminate\Support\Facades\Route;

Route::get('/state', [RoadmapController::class, 'state']);
Route::post('/import', [RoadmapController::class, 'import']);
Route::post('/sync', [RoadmapController::class, 'sync']);

Route::post('/items', [RoadmapController::class, 'storeItem']);
Route::put('/items/{id}', [RoadmapController::class, 'updateItem']);
Route::patch('/items/{id}', [RoadmapController::class, 'updateItem']);
Route::delete('/items/{id}', [RoadmapController::class, 'destroyItem']);
Route::delete('/products/{productId}/items', [RoadmapController::class, 'destroyItemsByProduct']);

Route::post('/products', [RoadmapController::class, 'storeProduct']);
Route::put('/products/{id}', [RoadmapController::class, 'updateProduct']);
Route::patch('/products/{id}', [RoadmapController::class, 'updateProduct']);
Route::delete('/products/{id}', [RoadmapController::class, 'destroyProduct']);
