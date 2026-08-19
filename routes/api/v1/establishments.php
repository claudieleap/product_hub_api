<?php

use App\Http\Controllers\Api\V1\EstablishmentAppointmentController;
use App\Http\Controllers\Api\V1\EstablishmentCommentController;
use App\Http\Controllers\Api\V1\EstablishmentController;
use App\Http\Controllers\Api\V1\EstablishmentMatrixController;
use App\Http\Controllers\Api\V1\OnboardingPhaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EstablishmentController::class, 'index']);
Route::post('/', [EstablishmentController::class, 'store']);
Route::get('/appointments', [EstablishmentAppointmentController::class, 'index']);

Route::middleware(['auth.token', 'admin'])->group(function () {
    Route::post('/import', [EstablishmentController::class, 'import']);
});

Route::prefix('phases')->group(function () {
    Route::get('/', [OnboardingPhaseController::class, 'index']);
    Route::post('/', [OnboardingPhaseController::class, 'store']);
    Route::put('/{id}', [OnboardingPhaseController::class, 'update']);
    Route::patch('/{id}', [OnboardingPhaseController::class, 'update']);
    Route::delete('/{id}', [OnboardingPhaseController::class, 'destroy']);
});

Route::get('/{id}', [EstablishmentController::class, 'show']);
Route::put('/{id}', [EstablishmentController::class, 'update']);
Route::patch('/{id}', [EstablishmentController::class, 'update']);
Route::delete('/{id}', [EstablishmentController::class, 'destroy']);

Route::middleware('auth.token')->group(function () {
    Route::get('/{id}/comments', [EstablishmentCommentController::class, 'index']);
    Route::post('/{id}/comments', [EstablishmentCommentController::class, 'store']);
    Route::get('/{id}/appointments', [EstablishmentAppointmentController::class, 'forEstablishment']);
    Route::post('/{id}/appointments', [EstablishmentAppointmentController::class, 'store']);
    Route::put('/{establishmentId}/appointments/{appointmentId}', [EstablishmentAppointmentController::class, 'update']);
    Route::patch('/{establishmentId}/appointments/{appointmentId}', [EstablishmentAppointmentController::class, 'update']);
    Route::delete('/{establishmentId}/appointments/{appointmentId}', [EstablishmentAppointmentController::class, 'destroy']);
});

Route::post('/{establishmentId}/units', [EstablishmentMatrixController::class, 'storeUnit']);
Route::put('/units/{unitId}', [EstablishmentMatrixController::class, 'updateUnit']);
Route::delete('/units/{unitId}', [EstablishmentMatrixController::class, 'destroyUnit']);

Route::post('/{establishmentId}/convenios', [EstablishmentMatrixController::class, 'storeConvenio']);
Route::put('/convenios/{convenioId}', [EstablishmentMatrixController::class, 'updateConvenio']);
Route::delete('/convenios/{convenioId}', [EstablishmentMatrixController::class, 'destroyConvenio']);

Route::put('/{establishmentId}/cells/{convenioId}/{unitId}', [EstablishmentMatrixController::class, 'updateCell']);
Route::patch('/{establishmentId}/cells/{convenioId}/{unitId}', [EstablishmentMatrixController::class, 'updateCell']);
