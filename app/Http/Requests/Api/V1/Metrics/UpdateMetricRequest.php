<?php

namespace App\Http\Requests\Api\V1\Metrics;

class UpdateMetricRequest extends MetricsRequest
{
    public function rules(): array
    {
        return [
            'groupId' => 'nullable|string|max:80',
            'label' => 'required|string|max:200',
        ];
    }
}
