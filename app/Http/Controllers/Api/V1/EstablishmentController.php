<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Establishment\ImportEstablishmentsRequest;
use App\Http\Requests\Api\V1\Establishment\StoreEstablishmentRequest;
use App\Http\Requests\Api\V1\Establishment\UpdateEstablishmentRequest;
use App\Services\EstablishmentImportService;
use App\Services\EstablishmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class EstablishmentController extends Controller
{
    public function __construct(
        private readonly EstablishmentService $establishmentService,
        private readonly EstablishmentImportService $establishmentImportService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->establishmentService->list(),
            'Estabelecimentos carregados com sucesso',
        ));
    }

    public function show(string $id): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->establishmentService->detail($id),
            'Estabelecimento carregado com sucesso',
        ));
    }

    public function store(StoreEstablishmentRequest $request): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->establishmentService->create($request->validated()),
            'Estabelecimento criado com sucesso',
        ));
    }

    public function update(UpdateEstablishmentRequest $request, string $id): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->establishmentService->update($id, $request->validated()),
            'Estabelecimento atualizado com sucesso',
        ));
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $this->establishmentService->delete($id);

            return ApiResponse::success(null, 'Estabelecimento excluído com sucesso');
        });
    }

    public function import(ImportEstablishmentsRequest $request): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->establishmentImportService->import($request->file('file')),
            'Importação concluída',
        ));
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
