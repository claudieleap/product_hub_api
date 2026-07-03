<?php

namespace App\Http\Requests\Api\V1\Metrics;

use Illuminate\Foundation\Http\FormRequest;

abstract class MetricsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
