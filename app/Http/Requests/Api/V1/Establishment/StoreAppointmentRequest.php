<?php

namespace App\Http\Requests\Api\V1\Establishment;

class StoreAppointmentRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'modality' => 'required|in:presencial,online',
            'location' => 'required_if:modality,presencial|nullable|string|max:255',
            'paymentLink' => 'required_if:modality,online|nullable|string|max:500',
            'responsavelId' => 'sometimes|nullable|in:mike,diego,carlinhos,matheus,pedro,clau,wendel,thiago,regina',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
