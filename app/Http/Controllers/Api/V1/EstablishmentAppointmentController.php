<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Establishment\StoreAppointmentRequest;
use App\Http\Requests\Api\V1\Establishment\UpdateAppointmentRequest;
use App\Services\EstablishmentAppointmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EstablishmentAppointmentController extends Controller
{
    public function __construct(
        private readonly EstablishmentAppointmentService $appointmentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $start = (string) $request->query('start', now()->startOfMonth()->toDateString());
        $end = (string) $request->query('end', now()->endOfMonth()->toDateString());

        return $this->handle(fn () => ApiResponse::success(
            $this->appointmentService->listInRange($start, $end),
            'Agendamentos carregados com sucesso',
        ));
    }

    public function forEstablishment(string $establishmentId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->appointmentService->listForEstablishment($establishmentId),
            'Agendamentos carregados com sucesso',
        ));
    }

    public function store(StoreAppointmentRequest $request, string $establishmentId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->appointmentService->create($establishmentId, $request->user(), $request->validated()),
            'Agendamento criado com sucesso',
        ));
    }

    public function update(UpdateAppointmentRequest $request, string $establishmentId, string $appointmentId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->appointmentService->update($establishmentId, $appointmentId, $request->validated()),
            'Agendamento atualizado com sucesso',
        ));
    }

    public function destroy(Request $request, string $establishmentId, string $appointmentId): JsonResponse
    {
        return $this->handle(function () use ($establishmentId, $appointmentId) {
            $this->appointmentService->delete($establishmentId, $appointmentId);

            return ApiResponse::success(null, 'Agendamento excluído com sucesso');
        });
    }

    private function handle(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error('Erro interno do servidor', 500);
        }
    }
}
