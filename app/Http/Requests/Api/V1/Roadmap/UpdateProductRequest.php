<?php

namespace App\Http\Requests\Api\V1\Roadmap;

class UpdateProductRequest extends RoadmapRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:80',
            'accent' => 'nullable|string|max:20',
            'isHidden' => 'nullable|boolean',
        ];
    }
}
