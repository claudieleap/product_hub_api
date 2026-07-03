<?php

namespace App\Http\Requests\Api\V1\Metrics;

class StoreGroupRequest extends MetricsRequest
{
    public function rules(): array
    {
        return [
            'id' => 'nullable|string|max:80',
            'label' => 'required|string|max:200',
        ];
    }
}
