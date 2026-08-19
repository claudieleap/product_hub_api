<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Metrics\StoreGroupRequest;
use App\Http\Requests\Api\V1\Metrics\StoreMetricRequest;
use App\Http\Requests\Api\V1\Metrics\UpdateGroupRequest;
use App\Http\Requests\Api\V1\Metrics\UpdateMetricRequest;
use App\Services\RoadmapMetricsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class RoadmapMetricsController extends Controller
{
    public function __construct(
        private readonly RoadmapMetricsService $metricsService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->metricsService->getCatalog(),
            'Catálogo de métricas carregado com sucesso',
        ));
    }

    public function storeGroup(StoreGroupRequest $request): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->metricsService->createGroup($request->validated()),
            'Grupo criado com sucesso',
        ));
    }

    public function updateGroup(UpdateGroupRequest $request, string $id): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->metricsService->updateGroup($id, $request->validated()),
            'Grupo atualizado com sucesso',
        ));
    }

    public function destroyGroup(string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $this->metricsService->deleteGroup($id);

            return ApiResponse::success(null, 'Grupo excluído com sucesso');
        });
    }

    public function storeMetric(StoreMetricRequest $request): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->metricsService->createMetric($request->validated()),
            'Métrica criada com sucesso',
        ));
    }

    public function updateMetric(UpdateMetricRequest $request, string $id): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->metricsService->updateMetric($id, $request->validated()),
            'Métrica atualizada com sucesso',
        ));
    }

    public function destroyMetric(string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $this->metricsService->deleteMetric($id);

            return ApiResponse::success(null, 'Métrica excluída com sucesso');
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
