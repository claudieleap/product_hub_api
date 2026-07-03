<?php

namespace App\Http\Requests\Api\V1\Metrics;

class StoreMetricRequest extends MetricsRequest
{
    public function rules(): array
    {
        return [
            'id' => 'nullable|string|max:80',
            'groupId' => 'required|string|max:80',
            'label' => 'required|string|max:200',
        ];
    }
}
