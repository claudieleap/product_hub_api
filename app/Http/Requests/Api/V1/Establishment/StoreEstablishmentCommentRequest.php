<?php

namespace App\Http\Requests\Api\V1\Establishment;

class StoreEstablishmentCommentRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'comment' => 'required|string|max:4000',
        ];
    }
}
