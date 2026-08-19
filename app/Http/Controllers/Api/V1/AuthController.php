<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuthToken;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const DEFAULT_PASSWORD = 'Mudar@2026';

    /* ---------- público ---------- */

    public function login(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $data = $request->validate([
                'login' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            $identifier = trim($data['login']);
            $user = User::where('username', $identifier)
                ->orWhere('email', $identifier)
                ->first();

            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return ApiResponse::error('Usuário ou senha inválidos', 401);
            }

            $token = $this->issueToken($user, $request->userAgent());

            return ApiResponse::success([
                'token' => $token,
                'user' => $this->userPayload($user),
            ], 'Login efetuado');
        });
    }

    /* ---------- autenticado ---------- */

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('auth_token');
        if ($token instanceof AuthToken) {
            $token->delete();
        }

        return ApiResponse::success(null, 'Sessão encerrada');
    }

    public function changePassword(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $data = $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = $request->user();
            $user->forceFill([
                'password' => Hash::make($data['password']),
                'must_change_password' => false,
            ])->save();

            // Encerra outras sessões por segurança, mantém a atual.
            $current = $request->attributes->get('auth_token');
            $user->tokens()->when($current, fn ($q) => $q->where('id', '!=', $current->id))->delete();

            return ApiResponse::success(['user' => $this->userPayload($user)], 'Senha atualizada');
        });
    }

    /* ---------- admin ---------- */

    public function listUsers(): JsonResponse
    {
        $users = User::orderBy('role')->orderBy('username')->get()
            ->map(fn (User $user) => $this->userPayload($user));

        return ApiResponse::success(['users' => $users]);
    }

    public function createUser(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'username' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:users,username'],
                'email' => ['nullable', 'email', 'unique:users,email'],
                'role' => ['required', Rule::in(['admin', 'user'])],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? $data['username'].'@aleevia.com.br',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'role' => $data['role'],
                'must_change_password' => true,
            ]);

            return ApiResponse::created(
                ['user' => $this->userPayload($user)],
                "Conta criada. Senha padrão: ".self::DEFAULT_PASSWORD
            );
        });
    }

    public function resetPassword(string $id): JsonResponse
    {
        return $this->handle(function () use ($id) {
            $user = User::findOrFail($id);
            $user->forceFill([
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'must_change_password' => true,
            ])->save();
            $user->tokens()->delete();

            return ApiResponse::success(null, "Senha redefinida para ".self::DEFAULT_PASSWORD);
        });
    }

    public function updateRole(Request $request, string $id): JsonResponse
    {
        return $this->handle(function () use ($request, $id) {
            $data = $request->validate([
                'role' => ['required', Rule::in(['admin', 'user'])],
            ]);

            $user = User::findOrFail($id);

            if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
                return ApiResponse::error('Você não pode remover seu próprio acesso de admin', 422);
            }

            $user->update(['role' => $data['role']]);

            return ApiResponse::success(['user' => $this->userPayload($user)], 'Perfil atualizado');
        });
    }

    public function deleteUser(Request $request, string $id): JsonResponse
    {
        return $this->handle(function () use ($request, $id) {
            $user = User::findOrFail($id);

            if ($user->id === $request->user()->id) {
                return ApiResponse::error('Você não pode excluir a própria conta', 422);
            }

            $user->delete();

            return ApiResponse::success(null, 'Conta removida');
        });
    }

    /* ---------- helpers ---------- */

    private function issueToken(User $user, ?string $agent): string
    {
        $plain = bin2hex(random_bytes(32));

        AuthToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plain),
            'name' => $agent ? substr($agent, 0, 120) : null,
            'last_used_at' => now(),
        ]);

        return $plain;
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'mustChangePassword' => (bool) $user->must_change_password,
        ];
    }

    private function handle(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (ValidationException $exception) {
            return ApiResponse::validationError($exception);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error('Registro não encontrado', 404);
        } catch (\Throwable $exception) {
            report($exception);

            return ApiResponse::error('Erro interno do servidor', 500);
        }
    }
}
