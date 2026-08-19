<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Establishment\PhaseRequest;
use App\Services\OnboardingPhaseService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class OnboardingPhaseController extends Controller
{
    public function __construct(
        private readonly OnboardingPhaseService $onboardingPhaseService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->onboardingPhaseService->list(),
            'Fases carregadas com sucesso',
        ));
    }

    public function store(PhaseRequest $request): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->onboardingPhaseService->create($request->validated()['title']),
            'Fase criada com sucesso',
        ));
    }

    public function update(PhaseRequest $request, string $id): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->onboardingPhaseService->update($id, $request->validated()),
            'Fase atualizada com sucesso',
        ));
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $this->onboardingPhaseService->delete($id);

            return ApiResponse::success(null, 'Fase excluída com sucesso');
        });
    }

    private function handle(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), $e->getStatusCode());
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (Throwable $e) {
            report($e);

            return ApiResponse::error('Erro interno do servidor', 500);
        }
    }
}
