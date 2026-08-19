<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveAuthToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();

        if (! $plain) {
            return ApiResponse::error('Não autenticado', 401);
        }

        $token = AuthToken::with('user')
            ->where('token', hash('sha256', $plain))
            ->first();

        if (! $token || ! $token->user) {
            return ApiResponse::error('Sessão inválida ou expirada', 401);
        }

        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('auth_token', $token);

        return $next($request);
    }
}
