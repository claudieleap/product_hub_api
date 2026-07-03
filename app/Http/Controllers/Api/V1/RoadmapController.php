<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Roadmap\ImportStateRequest;
use App\Http\Requests\Api\V1\Roadmap\StoreItemRequest;
use App\Http\Requests\Api\V1\Roadmap\StoreProductRequest;
use App\Http\Requests\Api\V1\Roadmap\SyncStateRequest;
use App\Http\Requests\Api\V1\Roadmap\UpdateItemRequest;
use App\Http\Requests\Api\V1\Roadmap\UpdateProductRequest;
use App\Services\RoadmapService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class RoadmapController extends Controller
{
    public function __construct(
        private readonly RoadmapService $roadmapService,
    ) {}

    public function state(string $type = 'saas'): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->roadmapService->getState($type),
            'Roadmap carregado com sucesso',
        ));
    }

    public function import(ImportStateRequest $request, string $type = 'saas'): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->roadmapService->importState($type, $request->validated()),
            'Roadmap importado com sucesso',
        ));
    }

    public function sync(SyncStateRequest $request, string $type = 'saas'): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->roadmapService->syncState($type, $request->validated()),
            'Roadmap sincronizado com sucesso',
        ));
    }

    public function storeItem(StoreItemRequest $request, string $type = 'saas'): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->roadmapService->createItem($type, $request->validated()),
            'Item criado com sucesso',
        ));
    }

    public function updateItem(UpdateItemRequest $request, string $type = 'saas', string $id = ''): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->roadmapService->updateItem($type, $id, $request->validated()),
            'Item atualizado com sucesso',
        ));
    }

    public function destroyItem(string $type = 'saas', string $id = ''): JsonResponse
    {
        return $this->handle(function () use ($type, $id) {
            $this->roadmapService->deleteItem($type, $id);

            return ApiResponse::success(null, 'Item excluído com sucesso');
        });
    }

    public function destroyItemsByProduct(string $type = 'saas', string $productId = ''): JsonResponse
    {
        return $this->handle(function () use ($type, $productId) {
            $count = $this->roadmapService->deleteItemsByProduct($type, $productId);

            return ApiResponse::success(['deleted' => $count], 'Itens excluídos com sucesso');
        });
    }

    public function finalizeDelivered(string $type = 'saas'): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->roadmapService->finalizeDelivered($type),
            'Entregas finalizadas com sucesso',
        ));
    }

    public function storeProduct(StoreProductRequest $request, string $type = 'saas'): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->roadmapService->createCustomProduct($type, $request->validated()),
            'Módulo criado com sucesso',
        ));
    }

    public function updateProduct(UpdateProductRequest $request, string $type = 'saas', string $id = ''): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->roadmapService->updateCustomProduct($type, $id, $request->validated()),
            'Módulo atualizado com sucesso',
        ));
    }

    public function destroyProduct(string $type = 'saas', string $id = ''): JsonResponse
    {
        return $this->handle(function () use ($type, $id) {
            $this->roadmapService->deleteCustomProduct($type, $id);

            return ApiResponse::success(null, 'Módulo excluído com sucesso');
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
