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
            'location' => 'sometimes|nullable|string|max:255',
            'paymentLink' => 'sometimes|nullable|string|max:500',
            'responsavelIds' => 'sometimes|array',
            'responsavelIds.*' => 'in:mike,diego,carlinhos,matheus,pedro,clau,wendel,thiago,regina',
            'notes' => 'sometimes|nullable|string|max:2000',
        ];
    }
}
