<?php

namespace App\Http\Requests\Api\V1\Establishment;

class PhaseRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:120',
            'hint' => 'sometimes|nullable|string|max:120',
            'orderIndex' => 'sometimes|integer|min:0',
        ];
    }
}
