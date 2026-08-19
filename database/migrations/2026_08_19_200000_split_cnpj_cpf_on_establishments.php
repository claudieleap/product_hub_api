<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CNPJ e CPF viram colunas separadas — um estabelecimento pode ter os dois
 * (ex.: CNPJ da clínica + CPF do responsável). A coluna única cnpj_cpf vira
 * `cnpj`; o valor migra para `cpf` quando pf_pj = 'F' (era pessoa física).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->renameColumn('cnpj_cpf', 'cnpj');
        });

        Schema::table('establishments', function (Blueprint $table) {
            $table->string('cpf', 20)->nullable()->after('cnpj');
        });

        DB::table('establishments')->where('pf_pj', 'F')->whereNotNull('cnpj')->get(['id', 'cnpj'])
            ->each(function ($row) {
                DB::table('establishments')->where('id', $row->id)->update([
                    'cpf' => $row->cnpj,
                    'cnpj' => null,
                ]);
            });
    }

    public function down(): void
    {
        // Estabelecimentos com cnpj E cpf preenchidos (o cenário que motivou o
        // split) não cabem de volta numa única coluna — cnpj_cpf fica com o
        // cnpj e o cpf desse estabelecimento é perdido no rollback.
        DB::table('establishments')->whereNotNull('cpf')->whereNull('cnpj')->get(['id', 'cpf'])
            ->each(function ($row) {
                DB::table('establishments')->where('id', $row->id)->update(['cnpj' => $row->cpf]);
            });

        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('cpf');
        });

        Schema::table('establishments', function (Blueprint $table) {
            $table->renameColumn('cnpj', 'cnpj_cpf');
        });
    }
};
