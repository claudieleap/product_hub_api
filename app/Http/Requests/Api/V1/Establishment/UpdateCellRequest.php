<?php

namespace App\Http\Requests\Api\V1\Establishment;

class UpdateCellRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'ativo' => 'sometimes|boolean',
            'modo' => 'sometimes|in:portal,fora',
            'portalLogin' => 'sometimes|nullable|string|max:255',
            'portalSenha' => 'sometimes|nullable|string|max:255',
            'detalhe' => 'sometimes|nullable|string|max:500',
            'conciliado' => 'sometimes|boolean',
            'ops' => 'sometimes|array',
        ];
    }
}
