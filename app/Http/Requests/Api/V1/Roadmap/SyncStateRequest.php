<?php

namespace App\Http\Requests\Api\V1\Roadmap;

class SyncStateRequest extends RoadmapRequest
{
    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.id' => 'required|string|max:120',
            'items.*.productId' => 'required|string|max:80',
            'items.*.priority' => 'required|in:alta,media,baixa,perfumaria',
            'items.*.title' => 'required|string|max:500',
            'items.*.notes' => 'nullable|string',
            'items.*.metrics' => 'nullable|array',
            'items.*.metrics.*' => 'string|max:80',
            'items.*.devStatus' => 'nullable|in:a_fazer,em_andamento,concluido',
            'items.*.createdAt' => 'nullable|date',
            'customProducts' => 'nullable|array',
            'customProducts.*.id' => 'required_with:customProducts|string|max:80',
            'customProducts.*.title' => 'required_with:customProducts|string|max:200',
            'customProducts.*.description' => 'nullable|string',
            'customProducts.*.icon' => 'nullable|string|max:80',
            'customProducts.*.accent' => 'nullable|string|max:20',
            'customProducts.*.createdAt' => 'nullable|date',
            'deletedSeedIds' => 'nullable|array',
            'deletedSeedIds.*' => 'string|max:120',
        ];
    }
}
