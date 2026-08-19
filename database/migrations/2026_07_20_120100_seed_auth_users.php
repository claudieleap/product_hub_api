<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Semeia as contas iniciais do Product Hub.
 * Idempotente: não recria/reseta quem já existe (username como chave).
 * Senha padrão: Mudar@2026 (must_change_password = true).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $password = Hash::make('Mudar@2026');

        $admins = ['pedro', 'claudiele', 'arruda'];
        $users = ['mike', 'diego', 'carlinhos', 'matheus', 'wendel', 'thiago', 'regina'];

        foreach ([['admin', $admins], ['user', $users]] as [$role, $list]) {
            foreach ($list as $username) {
                if (DB::table('users')->where('username', $username)->exists()) {
                    continue;
                }

                DB::table('users')->insert([
                    'name' => ucfirst($username),
                    'username' => $username,
                    'email' => $username.'@aleevia.com.br',
                    'password' => $password,
                    'role' => $role,
                    'must_change_password' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('username', ['pedro', 'claudiele', 'arruda', 'mike', 'diego', 'carlinhos', 'matheus', 'wendel', 'thiago', 'regina'])
            ->delete();
    }
};
