<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estabelecimento — registro único de clínica/hospital/prestador, compartilhado
 * pelas telas de Onboarding (implantação) e Pipeline comercial (prospecção).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('cnpj_cpf', 20)->nullable()->index();

            $table->string('fantasia', 255)->nullable();
            $table->string('razao_social', 255)->nullable();
            $table->string('kind', 20)->default('clinica');
            $table->json('projects')->nullable();
            $table->json('especialidades')->nullable();
            $table->string('classificacao', 120)->nullable();
            $table->string('grupo_econ', 255)->nullable();
            $table->string('municipio', 120)->nullable()->index();
            $table->string('uf', 2)->nullable();
            $table->string('bairro', 120)->nullable();
            $table->string('endereco', 255)->nullable();
            $table->string('num_endereco', 20)->nullable();
            $table->string('complemento', 120)->nullable();
            $table->string('cep', 15)->nullable();
            $table->string('ddd', 5)->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('divulgacao', 60)->nullable();
            $table->string('nat_nd', 60)->nullable();
            $table->string('pf_pj', 5)->nullable();

            // Pipeline comercial
            $table->string('stage_id', 40)->nullable()->index();
            $table->integer('order_index')->default(0);

            // Onboarding
            $table->string('onboarding_phase_id', 40)->nullable()->index();
            $table->integer('onboarding_order_index')->default(0);
            $table->string('onboarding_responsavel_id', 40)->nullable();

            $table->text('observacao')->default('');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishments');
    }
};
