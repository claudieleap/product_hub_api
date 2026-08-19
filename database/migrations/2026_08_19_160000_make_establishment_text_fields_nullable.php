<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O middleware padrão do Laravel (ConvertEmptyStringsToNull) converte string
 * vazia em null antes da validação — essas colunas precisam aceitar null
 * (campo "sem preenchimento" ainda), senão o update quebra com NOT NULL
 * constraint assim que o usuário limpa um campo de texto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->text('observacao')->nullable()->default(null)->change();
        });

        Schema::table('establishment_cells', function (Blueprint $table) {
            $table->string('portal_login', 255)->nullable()->default(null)->change();
            $table->string('portal_senha', 255)->nullable()->default(null)->change();
            $table->string('detalhe', 500)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('establishment_cells', function (Blueprint $table) {
            $table->string('detalhe', 500)->default('')->change();
            $table->string('portal_senha', 255)->default('')->change();
            $table->string('portal_login', 255)->default('')->change();
        });

        Schema::table('establishments', function (Blueprint $table) {
            $table->text('observacao')->default('')->change();
        });
    }
};
