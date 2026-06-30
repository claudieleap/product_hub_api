<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'product_hub_api',
            'version' => config('app.version', '0.1.0'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
