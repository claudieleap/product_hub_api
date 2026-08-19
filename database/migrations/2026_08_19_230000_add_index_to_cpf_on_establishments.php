<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cnpj herdou o índice da antiga cnpj_cpf; cpf ficou sem — o import
 * (~1M linhas) faz whereIn('cpf', ...) pra pular quem já existe, e isso
 * varria a tabela inteira sem índice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->index('cpf');
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropIndex(['cpf']);
        });
    }
};
