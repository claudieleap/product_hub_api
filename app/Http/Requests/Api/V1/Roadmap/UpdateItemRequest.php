<?php

namespace App\Http\Requests\Api\V1\Roadmap;

class UpdateItemRequest extends RoadmapRequest
{
    public function rules(): array
    {
        return [
            'productId' => 'sometimes|string|max:80',
            'priority' => 'sometimes|in:alta,media,baixa,perfumaria',
            'title' => 'sometimes|string|max:500',
            'notes' => 'nullable|string',
            'metrics' => 'nullable|array',
            'metrics.*' => 'string|max:80',
            'devStatus' => 'nullable|in:a_fazer,em_andamento,concluido',
        ];
    }
}
