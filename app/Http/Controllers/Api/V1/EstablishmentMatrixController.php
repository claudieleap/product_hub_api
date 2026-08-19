<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Establishment\NameRequest;
use App\Http\Requests\Api\V1\Establishment\UpdateCellRequest;
use App\Services\EstablishmentMatrixService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Throwable;

class EstablishmentMatrixController extends Controller
{
    public function __construct(
        private readonly EstablishmentMatrixService $matrixService,
    ) {}

    public function storeUnit(NameRequest $request, string $establishmentId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->matrixService->createUnit($establishmentId, 'un-'.Str::random(10), $request->validated()['name']),
            'Unidade criada com sucesso',
        ));
    }

    public function updateUnit(NameRequest $request, string $unitId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->matrixService->renameUnit($unitId, $request->validated()['name']),
            'Unidade atualizada com sucesso',
        ));
    }

    public function destroyUnit(string $unitId): JsonResponse
    {
        return $this->handle(function () use ($unitId) {
            $this->matrixService->deleteUnit($unitId);

            return ApiResponse::success(null, 'Unidade excluída com sucesso');
        });
    }

    public function storeConvenio(NameRequest $request, string $establishmentId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->matrixService->createConvenio($establishmentId, 'cv-'.Str::random(10), $request->validated()['name']),
            'Convênio criado com sucesso',
        ));
    }

    public function updateConvenio(NameRequest $request, string $convenioId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->matrixService->renameConvenio($convenioId, $request->validated()['name']),
            'Convênio atualizado com sucesso',
        ));
    }

    public function destroyConvenio(string $convenioId): JsonResponse
    {
        return $this->handle(function () use ($convenioId) {
            $this->matrixService->deleteConvenio($convenioId);

            return ApiResponse::success(null, 'Convênio excluído com sucesso');
        });
    }

    public function updateCell(UpdateCellRequest $request, string $establishmentId, string $convenioId, string $unitId): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->matrixService->upsertCell($establishmentId, $convenioId, $unitId, $request->validated()),
            'Célula atualizada com sucesso',
        ));
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
