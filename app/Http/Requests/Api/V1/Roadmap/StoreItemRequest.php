<?php

namespace App\Http\Requests\Api\V1\Roadmap;

class StoreItemRequest extends RoadmapRequest
{
    public function rules(): array
    {
        return [
            'id' => 'sometimes|string|max:120',
            'productId' => 'required|string|max:80',
            'priority' => 'required|in:alta,media,baixa,perfumaria,backlog',
            'backlogPriority' => 'nullable|in:alta,media,baixa,perfumaria',
            'title' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'metrics' => 'nullable|array',
            'metrics.*' => 'string|max:80',
            'devStatus' => 'nullable|in:a_fazer,em_andamento,concluido',
            'createdAt' => 'nullable|date',
        ];
    }
}
