<?php

use App\Http\Controllers\Api\V1\RoadmapMetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RoadmapMetricsController::class, 'index']);
Route::post('/groups', [RoadmapMetricsController::class, 'storeGroup']);
Route::put('/groups/{id}', [RoadmapMetricsController::class, 'updateGroup']);
Route::patch('/groups/{id}', [RoadmapMetricsController::class, 'updateGroup']);
Route::delete('/groups/{id}', [RoadmapMetricsController::class, 'destroyGroup']);
Route::post('/', [RoadmapMetricsController::class, 'storeMetric']);
Route::put('/{id}', [RoadmapMetricsController::class, 'updateMetric']);
Route::patch('/{id}', [RoadmapMetricsController::class, 'updateMetric']);
Route::delete('/{id}', [RoadmapMetricsController::class, 'destroyMetric']);
