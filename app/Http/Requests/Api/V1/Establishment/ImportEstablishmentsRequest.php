<?php

namespace App\Http\Requests\Api\V1\Establishment;

class ImportEstablishmentsRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls|max:102400',
        ];
    }
}
