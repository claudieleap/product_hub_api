<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? 'Operação realizada com sucesso',
            'data' => $data,
        ], $status);
    }

    public static function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::success($data, $message ?? 'Recurso criado com sucesso', 201);
    }

    public static function error(?string $message = null, int $status = 500, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? 'Erro interno do servidor',
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    public static function validationError(ValidationException $exception): JsonResponse
    {
        return self::error(
            'Dados inválidos',
            422,
            $exception->errors(),
        );
    }
}
