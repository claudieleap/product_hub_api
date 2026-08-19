<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Establishment\StoreEstablishmentCommentRequest;
use App\Services\EstablishmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class EstablishmentCommentController extends Controller
{
    public function __construct(
        private readonly EstablishmentService $establishmentService,
    ) {}

    public function index(string $id): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::success(
            $this->establishmentService->listComments($id),
            'Comentários carregados com sucesso',
        ));
    }

    public function store(StoreEstablishmentCommentRequest $request, string $id): JsonResponse
    {
        return $this->handle(fn () => ApiResponse::created(
            $this->establishmentService->addComment($id, $request->user(), $request->validated()['comment']),
            'Comentário adicionado com sucesso',
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
