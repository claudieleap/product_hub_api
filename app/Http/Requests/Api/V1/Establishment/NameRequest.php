<?php

namespace App\Http\Requests\Api\V1\Establishment;

/** Usado por unidades e convênios — o único campo editável é o nome. */
class NameRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
        ];
    }
}
