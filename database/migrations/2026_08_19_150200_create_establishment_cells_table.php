<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Célula da matriz convênio × unidade. `ops` guarda as 3 operações fixas
 * (Faturamento/DP/DC) como {status, descricao} — mesmo formato usado no
 * frontend (ver onboardingConfig.js), pra não precisar remapear a lógica de
 * semáforo já pronta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishment_cells', function (Blueprint $table) {
            $table->id();
            $table->string('establishment_id', 40)->index();
            $table->string('convenio_id', 40);
            $table->string('unit_id', 40);
            $table->boolean('ativo')->default(false);
            $table->string('modo', 20)->default('portal');
            $table->string('portal_login', 255)->default('');
            $table->string('portal_senha', 255)->default('');
            $table->string('detalhe', 500)->default('');
            $table->boolean('conciliado')->default(false);
            $table->json('ops')->nullable();
            $table->timestamps();

            $table->foreign('establishment_id')->references('id')->on('establishments')->cascadeOnDelete();
            $table->foreign('convenio_id')->references('id')->on('establishment_convenios')->cascadeOnDelete();
            $table->foreign('unit_id')->references('id')->on('establishment_units')->cascadeOnDelete();
            $table->unique(['convenio_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_cells');
    }
};
