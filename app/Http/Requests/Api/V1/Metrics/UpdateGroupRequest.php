<?php

namespace App\Http\Requests\Api\V1\Metrics;

class UpdateGroupRequest extends MetricsRequest
{
    public function rules(): array
    {
        return [
            'label' => 'required|string|max:200',
        ];
    }
}
