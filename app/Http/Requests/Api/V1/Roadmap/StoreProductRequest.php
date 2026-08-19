<?php

namespace App\Http\Requests\Api\V1\Roadmap;

class StoreProductRequest extends RoadmapRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|string|max:80',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:80',
            'accent' => 'nullable|string|max:20',
            'isHidden' => 'nullable|boolean',
            'createdAt' => 'nullable|date',
        ];
    }
}
